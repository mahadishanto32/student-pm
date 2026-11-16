<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectBook;
use App\Models\ProjectMark;
use App\Models\ProjectBookChapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class StudentProjectBookController extends Controller
{
    /** Directory (relative to public/) where chapter files are stored. */
    private const UPLOAD_DIR = 'uploads/book-chapters';

    /**
     * Allowed extensions for chapter documents.
     */
    private const ALLOWED_EXTS = [
        'pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx',
        'txt', 'csv', 'zip', 'rar', 'png', 'jpg', 'jpeg', 'gif',
    ];

    /**
     * Display a listing of project books for the logged-in student's projects.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $projectIds = $user->projects()->pluck('projects.id');

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

        $myMarks = ProjectMark::with(['distributions' => fn ($q) => $q->where('topic', 'Project Book')])
            ->where('student_id', Auth::id())
            ->whereIn('project_id', $projectIds)
            ->get()
            ->keyBy('project_id');

        return view('student.project-book.index', compact('projectBooks', 'myMarks'));
    }

    /**
     * Show the form for creating a new project book.
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $projectIds = $user->projects()->pluck('projects.id');

        $projects = Project::whereIn('id', $projectIds)
            ->whereDoesntHave('projectBook')
            ->get();

        return view('student.project-book.create', compact('projects'));
    }

    /**
     * Store a newly created project book (with chapters + documents).
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $projectIds = $user->projects()->pluck('projects.id');

        $validated = $request->validate([
            'project_id'                     => ['required', 'integer', 'in:' . $projectIds->implode(',')],
            'chapters'                       => ['nullable', 'array'],
            'chapters.*.chapter_no'          => ['nullable', 'integer', 'min:1'],
            'chapters.*.chapter_title'       => ['nullable', 'string', 'max:255'],
            'chapters.*.chapter_description' => ['nullable', 'string'],
            'chapters.*.documents'           => ['nullable', 'array'],
            'chapters.*.documents.*'         => [
                'file',
                'max:20480', // 20 MB each
                'mimes:' . implode(',', self::ALLOWED_EXTS),
            ],
        ]);

        if (ProjectBook::where('project_id', $validated['project_id'])->exists()) {
            return back()->withInput()->with('error', 'A book already exists for this project.');
        }

        DB::transaction(function () use ($validated) {
            $book = ProjectBook::create([
                'project_id' => $validated['project_id'],
            ]);

            $this->syncChapters($book, $validated['chapters'] ?? []);
        });

        return redirect()
            ->route('student.project-books.index')
            ->with('success', 'Project book created successfully.');
    }

    /**
     * Display the specified project book with its chapters.
     */
    public function show(ProjectBook $projectBook)
    {
        $this->authorizeBook($projectBook);

        $projectBook->load(['project', 'chapters' => function ($q) {
            $q->orderBy('chapter_no');
        }]);

        $myMark = ProjectMark::with(['distributions' => fn ($q) => $q->where('topic', 'Project Book')])
            ->where('project_id', $projectBook->project_id)
            ->where('student_id', Auth::id())
            ->first();

        return view('student.project-book.show', compact('projectBook', 'myMark'));
    }

    /**
     * Show the form for editing the specified project book.
     */
    public function edit(ProjectBook $projectBook)
    {
        $this->authorizeBook($projectBook);

        $user = Auth::user();
        $projectIds = $user->projects()->pluck('projects.id');

        $projects = Project::whereIn('id', $projectIds)->get();

        $projectBook->load(['chapters' => function ($q) {
            $q->orderBy('chapter_no');
        }]);

        return view('student.project-book.edit', compact('projectBook', 'projects'));
    }

    /**
     * Update the specified project book (chapters + documents).
     */
    public function update(Request $request, ProjectBook $projectBook)
    {
        $this->authorizeBook($projectBook);

        $validated = $request->validate([
            'project_id'                     => ['required', 'integer', 'exists:projects,id'],
            'chapters'                       => ['nullable', 'array'],
            'chapters.*.id'                  => ['nullable', 'integer'],
            'chapters.*.chapter_no'          => ['nullable', 'integer', 'min:1'],
            'chapters.*.chapter_title'       => ['nullable', 'string', 'max:255'],
            'chapters.*.chapter_description' => ['nullable', 'string'],
            'chapters.*.documents'           => ['nullable', 'array'],
            'chapters.*.documents.*'         => [
                'file',
                'max:20480',
                'mimes:' . implode(',', self::ALLOWED_EXTS),
            ],
            'chapters.*.remove_documents'    => ['nullable', 'array'],
            'chapters.*.remove_documents.*'  => ['string'],
        ]);

        DB::transaction(function () use ($validated, $projectBook) {
            $projectBook->update([
                'project_id' => $validated['project_id'],
            ]);

            $projectBook->chapters()->delete();
            $this->syncChapters($projectBook, $validated['chapters'] ?? []);
        });

        return redirect()
            ->route('student.project-books.index')
            ->with('success', 'Project book updated successfully.');
    }

    /**
     * Remove the specified project book (and its chapters + files).
     */
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
            ->route('student.project-books.index')
            ->with('success', 'Project book deleted successfully.');
    }

    /* ------------------------------------------------------------------ */
    /* Helpers                                                            */
    /* ------------------------------------------------------------------ */

    /**
     * Insert chapters into the given book (with documents) ignoring rows with no title.
     */
    protected function syncChapters(ProjectBook $book, array $chapters): void
    {
        $rows = [];
        foreach ($chapters as $chapter) {
            $title = trim((string) ($chapter['chapter_title'] ?? ''));
            if ($title === '') {
                continue;
            }

            // Start with any existing (kept) docs from the edit form
            $documents = [];

            // If the form supplied an id (edit mode), pull existing docs from DB
            if (!empty($chapter['id'])) {
                $existing = ProjectBookChapter::find($chapter['id']);
                if ($existing) {
                    $existingDocs = $existing->documents ?? [];

                    // Remove the ones the user marked for deletion
                    $toRemove = $chapter['remove_documents'] ?? [];
                    if (!empty($toRemove)) {
                        $this->deleteChapterFiles($toRemove);
                        $existingDocs = array_values(array_diff($existingDocs, $toRemove));
                    }

                    $documents = $existingDocs;
                }
            }

            // Handle newly uploaded files
            $uploaded = $chapter['documents'] ?? [];
            // Laravel gives us an array under [documents] only when multiple=True,
            // but normalize in case a single file was sent.
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

        if (! empty($rows)) {
            ProjectBookChapter::insert($rows);
        }
    }

    /**
     * Save an uploaded file to public/uploads/book-chapters/ and return the relative path.
     */
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

    /**
     * Delete files (relative paths inside public/) from disk.
     */
    protected function deleteChapterFiles(array $paths): void
    {
        foreach ($paths as $path) {
            // Only ever delete inside our upload directory
            if (!str_starts_with($path, self::UPLOAD_DIR . '/')) {
                continue;
            }
            $full = public_path($path);
            if (File::exists($full)) {
                File::delete($full);
            }
        }
    }

    /**
     * Ensure the current user is allowed to touch this book.
     */
    protected function authorizeBook(ProjectBook $projectBook): void
    {
        $user = Auth::user();

        $isMember = $user->projects()
            ->where('projects.id', $projectBook->project_id)
            ->exists();

        abort_unless($isMember, 403, 'You are not allowed to access this project book.');
    }
}