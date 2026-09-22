<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectBook;
use App\Models\ProjectBookChapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TeacherProjectBookController extends Controller
{
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

        return view('teachers.project-book.index', compact('projectBooks'));
    }

    /* ------------------------------------------------------------------ */
    /* SHOW                                                               */
    /* ------------------------------------------------------------------ */
    public function show(ProjectBook $projectBook)
    {
        $this->authorizeBook($projectBook);

        $projectBook->load(['project', 'chapters' => function ($q) {
            $q->orderBy('chapter_no');
        }]);

        return view('teachers.project-book.show', compact('projectBook'));
    }

    /* ------------------------------------------------------------------ */
    /* EDIT — status is editable here                                     */
    /* ------------------------------------------------------------------ */
    public function edit(ProjectBook $projectBook)
    {
        $this->authorizeBook($projectBook);

        $statuses = self::STATUSES;

        $projectBook->load(['chapters' => function ($q) {
            $q->orderBy('chapter_no');
        }]);

        return view('teachers.project-book.edit', compact('projectBook', 'statuses'));
    }

    /* ------------------------------------------------------------------ */
    /* UPDATE — teacher can change status + chapters                      */
    /* ------------------------------------------------------------------ */
    public function update(Request $request, ProjectBook $projectBook)
    {
        $this->authorizeBook($projectBook);

        $validated = $request->validate([
            'status'                         => ['required', Rule::in(self::STATUSES)],
            'chapters'                       => ['nullable', 'array'],
            'chapters.*.chapter_no'          => ['nullable', 'integer', 'min:1'],
            'chapters.*.chapter_title'       => ['nullable', 'string', 'max:255'],
            'chapters.*.chapter_description' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated, $projectBook) {
            // project_id is NOT changeable (teacher cannot move book to another project)
            $projectBook->update([
                'status' => $validated['status'],
            ]);

            $projectBook->chapters()->delete();
            $this->syncChapters($projectBook, $validated['chapters'] ?? []);
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
}