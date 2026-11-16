<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectBook;
use App\Models\ProjectBookChapter;
use App\Models\ProjectMark;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class TeacherProjectBookController extends Controller
{
    /** Directory (relative to public/) where chapter files are stored. */
    private const UPLOAD_DIR = 'uploads/book-chapters';

    /** Allowed extensions for chapter documents. */
    private const ALLOWED_EXTS = [
        'pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx',
        'txt', 'csv', 'zip', 'rar', 'png', 'jpg', 'jpeg', 'gif',
    ];

    /** Statuses the teacher is allowed to set. */
    private const STATUSES = [
        'pending', 'working', 'approved', 'completed', 'rejected', 'cancelled',
    ];

    /* ------------------------------------------------------------------ */
    /* Index                                                              */
    /* ------------------------------------------------------------------ */
    public function index(Request $request)
    {
        $teacherId = Auth::id();

        $projectIds = Project::query()
            ->where(function ($q) use ($teacherId) {
                $q->where('assigned_teacher', $teacherId)
                  ->orWhereHas('teamMembers', function ($sub) use ($teacherId) {
                      $sub->where('users.id', $teacherId);
                  });
            })
            ->pluck('id');

        $query = ProjectBook::with('project')
            ->whereIn('project_id', $projectIds);

        if ($search = $request->input('search')) {
            $query->whereHas('project', function ($q) use ($search) {
                $q->where('project_name', 'like', "%{$search}%")
                  ->orWhere('project_topic', 'like', "%{$search}%")
                  ->orWhere('group_number', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $projectBooks = $query->latest()->paginate(10);

        $marksSummary = ProjectMark::with(['distributions' => fn ($q) => $q->where('topic', 'Project Book')])
            ->whereIn('project_id', $projectBooks->pluck('project_id')->unique())
            ->get()
            ->groupBy('project_id')
            ->map(function ($rows) {
                $given = $rows
                    ->map(fn ($m) => $m->distributions->first()?->given_marks)
                    ->filter(fn ($v) => $v !== null)
                    ->map(fn ($v) => (float) $v);

                return [
                    'marked' => $given->count(),
                    'avg'    => $given->count() ? round($given->avg(), 2) : null,
                ];
            });

        return view('teachers.project-book.index', compact('projectBooks', 'marksSummary'));
    }

    /* ------------------------------------------------------------------ */
    /* Show — the missing method                                          */
    /* ------------------------------------------------------------------ */
    public function show(ProjectBook $projectBook)
    {
        $this->authorizeBook($projectBook);

        $projectBook->load([
            'project',
            'chapters' => fn ($q) => $q->orderBy('chapter_no'),
        ]);

        // Students in this project's team
        $students = $projectBook->project->teamMembers()
            ->where('users.role', 'student') // adjust if your role column differs
            ->orderBy('users.name')
            ->get();

        // If no students matched that role filter, fall back to the raw team
        if ($students->isEmpty()) {
            $students = $projectBook->project->teamMembers()->orderBy('users.name')->get();
        }

        // Existing ProjectMark rows (with "Project Book" distribution) keyed by student_id
        $marks = ProjectMark::with(['distributions' => fn ($q) => $q->where('topic', 'Project Book')])
            ->where('project_id', $projectBook->project_id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        return view('teachers.project-book.show', compact('projectBook', 'students', 'marks'));
    }

    /* ------------------------------------------------------------------ */
    /* Edit                                                               */
    /* ------------------------------------------------------------------ */
    public function edit(ProjectBook $projectBook)
    {
        $this->authorizeBook($projectBook);

        $projectBook->load([
            'project',
            'chapters' => fn ($q) => $q->orderBy('chapter_no'),
        ]);

        $students = $projectBook->project->teamMembers()
            ->where('users.role', 'student')
            ->orderBy('users.name')
            ->get();

        if ($students->isEmpty()) {
            $students = $projectBook->project->teamMembers()->orderBy('users.name')->get();
        }

        $existingMarks = ProjectMark::with(['distributions' => fn ($q) => $q->where('topic', 'Project Book')])
            ->where('project_id', $projectBook->project_id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        $statuses = self::STATUSES;

        return view('teachers.project-book.edit', compact(
            'projectBook',
            'students',
            'existingMarks',
            'statuses'
        ));
    }

    /* ------------------------------------------------------------------ */
    /* Update                                                             */
    /* ------------------------------------------------------------------ */
    public function update(Request $request, ProjectBook $projectBook)
    {
        $this->authorizeBook($projectBook);

        $validated = $request->validate([
            'status'                         => ['required', 'string', 'in:' . implode(',', self::STATUSES)],
            'marks'                          => ['nullable', 'array'],
            'marks.*.given_marks'            => ['nullable', 'numeric', 'min:0', 'max:' . ProjectMark::MARKS_PER_TOPIC],
            'marks.*.remarks'                => ['nullable', 'string', 'max:1000'],
            'chapters'                       => ['nullable', 'array'],
            'chapters.*.id'                  => ['nullable', 'integer'],
            'chapters.*.chapter_no'          => ['nullable', 'integer', 'min:1'],
            'chapters.*.chapter_title'       => ['nullable', 'string', 'max:255'],
            'chapters.*.chapter_description' => ['nullable', 'string'],
            'chapters.*.documents'           => ['nullable', 'array'],
            'chapters.*.documents.*'         => [
                'file',
                'max:20480', // 20 MB each
                'mimes:' . implode(',', self::ALLOWED_EXTS),
            ],
            'chapters.*.remove_documents'    => ['nullable', 'array'],
            'chapters.*.remove_documents.*'  => ['string'],
        ]);

        DB::transaction(function () use ($validated, $projectBook) {
            $projectBook->update(['status' => $validated['status']]);

            // --- marks (only when completed) ---
            if ($validated['status'] === 'completed' && !empty($validated['marks'])) {
                $this->syncMarks($projectBook, $validated['marks']);
            }

            // --- chapters + documents ---
            $existingDocsById = $projectBook->chapters
                ->mapWithKeys(fn ($c) => [$c->id => $c->documents ?? []])
                ->all();

            $projectBook->chapters()->delete();
            $this->syncChapters($projectBook, $validated['chapters'] ?? [], $existingDocsById);
        });

        return redirect()
            ->route('teachers.project-books.index')
            ->with('success', 'Project book updated successfully.');
    }

    /* ------------------------------------------------------------------ */
    /* Destroy                                                            */
    /* ------------------------------------------------------------------ */
    public function destroy(ProjectBook $projectBook)
    {
        $this->authorizeBook($projectBook);

        DB::transaction(function () use ($projectBook) {
            foreach ($projectBook->chapters as $chapter) {
                $this->deleteChapterFiles($chapter->documents ?? []);
            }
            $projectBook->chapters()->delete();
            $projectBook->delete();
        });

        return redirect()
            ->route('teachers.project-books.index')
            ->with('success', 'Project book deleted successfully.');
    }

    /* ------------------------------------------------------------------ */
    /* Helpers                                                            */
    /* ------------------------------------------------------------------ */

    /**
     * Upsert ProjectMark + distribution for each student in the given marks array.
     * Assumes ProjectMark has a "Project Book" distribution row when completed.
     */
    protected function syncMarks(ProjectBook $book, array $marks): void
    {
        foreach ($marks as $studentId => $row) {
            if (!isset($row['given_marks']) || $row['given_marks'] === '' || $row['given_marks'] === null) {
                continue;
            }

            $pm = ProjectMark::firstOrCreate(
                [
                    'project_id' => $book->project_id,
                    'student_id' => (int) $studentId,
                ],
                [
                    // any additional required columns should be added here if needed
                ]
            );

            if (!empty($row['remarks'])) {
                $pm->remarks = $row['remarks'];
                $pm->save();
            }

            // Upsert the "Project Book" distribution row
            $pm->distributions()->updateOrCreate(
                ['topic' => 'Project Book'],
                [
                    'given_marks' => $row['given_marks'],
                    'out_of'      => ProjectMark::MARKS_PER_TOPIC,
                ]
            );
        }
    }

    protected function syncChapters(ProjectBook $book, array $chapters, array $existingDocsById = []): void
    {
        $rows = [];

        foreach ($chapters as $chapter) {
            $title = trim((string) ($chapter['chapter_title'] ?? ''));
            if ($title === '') {
                continue;
            }

            $documents = [];
            if (!empty($chapter['id']) && isset($existingDocsById[$chapter['id']])) {
                $documents = $existingDocsById[$chapter['id']];
            }

            $toRemove = $chapter['remove_documents'] ?? [];
            if (!empty($toRemove)) {
                $this->deleteChapterFiles($toRemove);
                $documents = array_values(array_diff($documents, $toRemove));
            }

            $uploaded = $chapter['documents'] ?? [];
            if (!is_array($uploaded)) {
                $uploaded = $uploaded ? [$uploaded] : [];
            }
            foreach ($uploaded as $file) {
                if (!$file || !$file->isValid()) {
                    continue;
                }
                $documents[] = $this->storeChapterFile($file);
            }

            $rows[] = [
                'project_book_id'     => $book->id,
                'chapter_no'          => (int) ($chapter['chapter_no'] ?? (count($rows) + 1)),
                'chapter_title'       => $title,
                'chapter_description' => $chapter['chapter_description'] ?? null,
                'documents'           => !empty($documents) ? json_encode($documents) : null,
                'created_at'          => now(),
                'updated_at'          => now(),
            ];
        }

        if (!empty($rows)) {
            ProjectBookChapter::insert($rows);
        }
    }

    protected function storeChapterFile($file): string
    {
        $dir = public_path(self::UPLOAD_DIR);
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $name = now()->format('Ymd_His') . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $file->move($dir, $name);

        return self::UPLOAD_DIR . '/' . $name;
    }

    protected function deleteChapterFiles(array $paths): void
    {
        foreach ($paths as $path) {
            if (!str_starts_with($path, self::UPLOAD_DIR . '/')) {
                continue;
            }
            $full = public_path($path);
            if (File::exists($full)) {
                File::delete($full);
            }
        }
    }

    protected function authorizeBook(ProjectBook $projectBook): void
    {
        $teacherId = Auth::id();

        $canAccess = Project::where('id', $projectBook->project_id)
            ->where(function ($q) use ($teacherId) {
                $q->where('assigned_teacher', $teacherId)
                  ->orWhereHas('teamMembers', function ($sub) use ($teacherId) {
                      $sub->where('users.id', $teacherId);
                  });
            })
            ->exists();

        abort_unless($canAccess, 403, 'You are not allowed to access this project book.');
    }
}