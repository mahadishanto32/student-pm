<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectBook;
use App\Models\ProjectBookChapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentProjectBookController extends Controller
{
    /**
     * Display a listing of project books for the logged-in student's projects.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get project ids the student is part of
        $projectIds = $user->projects()->pluck('projects.id');

        $query = ProjectBook::with('project')
            ->whereIn('project_id', $projectIds);

        // Search by project name / topic / group number
        if ($search = $request->input('search')) {
            $query->whereHas('project', function ($q) use ($search) {
                $q->where('project_name', 'like', "%{$search}%")
                  ->orWhere('project_topic', 'like', "%{$search}%")
                  ->orWhere('group_number', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $projectBooks = $query->latest()->paginate(10);

        return view('student.project-book.index', compact('projectBooks'));
    }

    /**
     * Show the form for creating a new project book.
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $projectIds = $user->projects()->pluck('projects.id');

        // Only projects that don't yet have a book
        $projects = Project::whereIn('id', $projectIds)
            ->whereDoesntHave('projectBook')
            ->get();

        return view('student.project-book.create', compact('projects'));
    }

    /**
     * Store a newly created project book (with chapters).
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $projectIds = $user->projects()->pluck('projects.id');

        $validated = $request->validate([
            'project_id'                       => ['required', 'integer', 'in:' . $projectIds->implode(',')],
            'chapters'                         => ['nullable', 'array'],
            'chapters.*.chapter_no'            => ['nullable', 'integer', 'min:1'],
            'chapters.*.chapter_title'         => ['nullable', 'string', 'max:255'],
            'chapters.*.chapter_description'   => ['nullable', 'string'],
        ]);

        // Prevent duplicate book for same project
        if (ProjectBook::where('project_id', $validated['project_id'])->exists()) {
            return back()->withInput()->with('error', 'A book already exists for this project.');
        }

        DB::transaction(function () use ($validated) {
            $book = ProjectBook::create([
                'project_id' => $validated['project_id'],
                // status is NOT set by the user; DB default = pending
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

        return view('student.project-book.show', compact('projectBook'));
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
     * Update the specified project book (and its chapters).
     */
    public function update(Request $request, ProjectBook $projectBook)
    {
        $this->authorizeBook($projectBook);

        $validated = $request->validate([
            'project_id'                       => ['required', 'integer', 'exists:projects,id'],
            'chapters'                         => ['nullable', 'array'],
            'chapters.*.chapter_no'            => ['nullable', 'integer', 'min:1'],
            'chapters.*.chapter_title'         => ['nullable', 'string', 'max:255'],
            'chapters.*.chapter_description'   => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated, $projectBook) {
            // Update project_id only (status is never touched here)
            $projectBook->update([
                'project_id' => $validated['project_id'],
            ]);

            // Remove old chapters, re-insert
            $projectBook->chapters()->delete();
            $this->syncChapters($projectBook, $validated['chapters'] ?? []);
        });

        return redirect()
            ->route('student.project-books.index')
            ->with('success', 'Project book updated successfully.');
    }

    /**
     * Remove the specified project book (and its chapters).
     */
    public function destroy(ProjectBook $projectBook)
    {
        $this->authorizeBook($projectBook);

        DB::transaction(function () use ($projectBook) {
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
     * Insert chapters into the given book, ignoring rows with no title.
     */
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