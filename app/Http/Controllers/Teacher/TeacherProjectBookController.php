<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectBook;
use App\Http\Controllers\Concerns\SavesStudentMarks;
use App\Models\ProjectMark;
use App\Models\ProjectBookChapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TeacherProjectBookController extends Controller
{
    use SavesStudentMarks;

    /** Allowed status values. */
    public const STATUSES = [
        'approved', 'pending', 'working', 'completed', 'rejected', 'cancelled',
    ];

    /* ------------------------------------------------------------------ */
    /* INDEX — books of projects the teacher supervises / is part of      */
    /* ------------------------------------------------------------------ */
    public function index(Request $request)
    {
        $teacherId = Auth::id();

        // Project IDs the teacher can access:
        //  - assigned_teacher = me  (supervisor)
        //  - OR in project_user pivot
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
    /* SHOW                                                               */
    /* ------------------------------------------------------------------ */
    public function show(ProjectBook $projectBook)
    {
        $this->authorizeBook($projectBook);

        $projectBook->load(['project.teamMembers', 'chapters' => fn ($q) => $q->orderBy('chapter_no')]);

        $students = $this->studentsOf($projectBook);

        $marks = ProjectMark::with(['distributions' => fn ($q) => $q->where('topic', 'Project Book')])
            ->where('project_id', $projectBook->project_id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        return view('teachers.project-book.show', compact('projectBook', 'students', 'marks'));
    }

    /* ------------------------------------------------------------------ */
    /* EDIT — status is editable here                                     */
    /* ------------------------------------------------------------------ */
    public function edit(ProjectBook $projectBook)
    {
        $this->authorizeBook($projectBook);

        $statuses = self::STATUSES;

        $projectBook->load([
            'project.teamMembers',
            'chapters' => fn ($q) => $q->orderBy('chapter_no'),
        ]);

        $students = $this->studentsOf($projectBook);

        $existingMarks = ProjectMark::with(['distributions' => fn ($q) => $q->where('topic', 'Project Book')])
            ->where('project_id', $projectBook->project_id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        return view('teachers.project-book.edit', compact('projectBook', 'statuses', 'students', 'existingMarks'));
    }

    /* ------------------------------------------------------------------ */
    /* UPDATE — teacher can change status + chapters                      */
    /* ------------------------------------------------------------------ */
    public function update(Request $request, ProjectBook $projectBook)
    {
        $this->authorizeBook($projectBook);

        $rules = [
            'status'                         => ['required', Rule::in(self::STATUSES)],
            'chapters'                       => ['nullable', 'array'],
            'chapters.*.chapter_no'          => ['nullable', 'integer', 'min:1'],
            'chapters.*.chapter_title'       => ['nullable', 'string', 'max:255'],
            'chapters.*.chapter_description' => ['nullable', 'string'],
        ];

        $isCompleted = $request->input('status') === 'completed';

        if ($isCompleted) {
            $rules += [
                'marks'               => ['required', 'array'],
                'marks.*.given_marks' => ['required', 'numeric', 'min:0', 'max:' . ProjectMark::MARKS_PER_TOPIC],
                'marks.*.remarks'     => ['nullable', 'string', 'max:2000'],
            ];
        }

        $validated = $request->validate($rules, [
            'marks.*.given_marks.required' => 'Please enter marks for every student.',
            'marks.*.given_marks.max'      => 'Marks cannot exceed ' . ProjectMark::MARKS_PER_TOPIC . '.',
        ]);

        DB::transaction(function () use ($validated, $projectBook, $isCompleted, $request) {
            $projectBook->update(['status' => $validated['status']]);

            $projectBook->chapters()->delete();
            $this->syncChapters($projectBook, $validated['chapters'] ?? []);

            if ($isCompleted) {
                $projectBook->load('project.teamMembers');

                $this->saveTopicMarks(
                    $projectBook->project_id,
                    $this->studentsOf($projectBook)->pluck('id')->all(),
                    'Project Book',
                    $request->input('marks', [])
                );
            }
        });

        return redirect()
            ->route('teachers.project-books.index')
            ->with('success', 'Project book updated successfully.');
    }

    /* ------------------------------------------------------------------ */
    /* DESTROY                                                            */
    /* ------------------------------------------------------------------ */
    public function destroy(ProjectBook $projectBook)
    {
        $this->authorizeBook($projectBook);

        DB::transaction(function () use ($projectBook) {
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
    protected function syncChapters(ProjectBook $book, array $chapters): void
    {
        $rows = [];
        foreach ($chapters as $chapter) {
            $title = trim((string) ($chapter['chapter_title'] ?? ''));
            if ($title === '') {
                continue;
            }

            $rows[] = [
                'project_book_id'     => $book->id,
                'chapter_no'          => (int) ($chapter['chapter_no'] ?? (count($rows) + 1)),
                'chapter_title'       => $title,
                'chapter_description' => $chapter['chapter_description'] ?? null,
                'created_at'          => now(),
                'updated_at'          => now(),
            ];
        }

        if (! empty($rows)) {
            ProjectBookChapter::insert($rows);
        }
    }

    /**
     * A teacher may access a project's book if they are the assigned teacher
     * OR a member of that project via the project_user pivot.
     */
    protected function authorizeBook(ProjectBook $projectBook): void
    {
        $teacherId = Auth::id();

        $allowed = Project::query()
            ->where('id', $projectBook->project_id)
            ->where(function ($q) use ($teacherId) {
                $q->where('assigned_teacher', $teacherId)
                  ->orWhereHas('teamMembers', function ($sub) use ($teacherId) {
                      $sub->where('users.id', $teacherId);
                  });
            })
            ->exists();

        abort_unless($allowed, 403, 'You are not allowed to access this project book.');
    }

    /** Students of the project (the logged-in teacher is never marked). */
    protected function studentsOf(ProjectBook $projectBook)
    {
        return $projectBook->project->teamMembers
            ->reject(fn ($u) => $u->id === Auth::id())
            ->values();
        // If users have a role column, filter on it too, e.g.
        // ->filter(fn ($u) => $u->role === 'student')
    }
}