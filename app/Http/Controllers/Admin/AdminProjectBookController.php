<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectBook;
use App\Models\ProjectBookChapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminProjectBookController extends Controller
{
    /** Allowed status values. */
    public const STATUSES = [
        'approved', 'pending', 'working', 'completed', 'rejected', 'cancelled',
    ];

    /* ------------------------------------------------------------------ */
    /* INDEX — admin sees ALL project books                               */
    /* ------------------------------------------------------------------ */
    public function index(Request $request)
    {
        $query = ProjectBook::with('project.teamMembers');

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

        return view('admin.project-book.index', compact('projectBooks'));
    }

    /* ------------------------------------------------------------------ */
    /* CREATE — pick a project (student team) that has no book yet        */
    /* ------------------------------------------------------------------ */
    public function create()
    {
        // Projects without a book, whose team has at least one student
        $projects = Project::with(['teamMembers' => function ($q) {
                $q->where('role', 'student');
            }])
            ->whereDoesntHave('projectBook')
            ->get();

        $statuses = self::STATUSES;

        return view('admin.project-book.create', compact('projects', 'statuses'));
    }

    /* ------------------------------------------------------------------ */
    /* STORE                                                              */
    /* ------------------------------------------------------------------ */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id'                     => ['required', 'integer', 'exists:projects,id'],
            'status'                         => ['required', Rule::in(self::STATUSES)],
            'chapters'                       => ['nullable', 'array'],
            'chapters.*.chapter_no'          => ['nullable', 'integer', 'min:1'],
            'chapters.*.chapter_title'       => ['nullable', 'string', 'max:255'],
            'chapters.*.chapter_description' => ['nullable', 'string'],
        ]);

        // One book per project
        if (ProjectBook::where('project_id', $validated['project_id'])->exists()) {
            return back()->withInput()->with('error', 'A book already exists for this project.');
        }

        DB::transaction(function () use ($validated) {
            $book = ProjectBook::create([
                'project_id' => $validated['project_id'],
                'status'     => $validated['status'],
            ]);

            $this->syncChapters($book, $validated['chapters'] ?? []);
        });

        return redirect()
            ->route('admin.project-books.index')
            ->with('success', 'Project book created successfully.');
    }

    /* ------------------------------------------------------------------ */
    /* SHOW                                                               */
    /* ------------------------------------------------------------------ */
    public function show(ProjectBook $projectBook)
    {
        $projectBook->load([
            'project.teamMembers',
            'chapters' => fn ($q) => $q->orderBy('chapter_no'),
        ]);

        return view('admin.project-book.show', compact('projectBook'));
    }

    /* ------------------------------------------------------------------ */
    /* EDIT                                                               */
    /* ------------------------------------------------------------------ */
    public function edit(ProjectBook $projectBook)
    {
        $projectBook->load(['chapters' => fn ($q) => $q->orderBy('chapter_no')]);

        // For the project dropdown: current project + any project without a book
        $projects = Project::with(['teamMembers' => function ($q) {
                $q->where('role', 'student');
            }])
            ->where(function ($q) use ($projectBook) {
                $q->where('id', $projectBook->project_id)
                  ->orWhereDoesntHave('projectBook');
            })
            ->get();

        $statuses = self::STATUSES;

        return view('admin.project-book.edit', compact('projectBook', 'projects', 'statuses'));
    }

    /* ------------------------------------------------------------------ */
    /* UPDATE                                                             */
    /* ------------------------------------------------------------------ */
    public function update(Request $request, ProjectBook $projectBook)
    {
        $validated = $request->validate([
            'project_id'                     => ['required', 'integer', 'exists:projects,id'],
            'status'                         => ['required', Rule::in(self::STATUSES)],
            'chapters'                       => ['nullable', 'array'],
            'chapters.*.chapter_no'          => ['nullable', 'integer', 'min:1'],
            'chapters.*.chapter_title'       => ['nullable', 'string', 'max:255'],
            'chapters.*.chapter_description' => ['nullable', 'string'],
        ]);

        // Prevent duplicate book for a different project
        $conflict = ProjectBook::where('project_id', $validated['project_id'])
            ->where('id', '!=', $projectBook->id)
            ->exists();

        if ($conflict) {
            return back()->withInput()->with('error', 'Another book already exists for that project.');
        }

        DB::transaction(function () use ($validated, $projectBook) {
            $projectBook->update([
                'project_id' => $validated['project_id'],
                'status'     => $validated['status'],
            ]);

            $projectBook->chapters()->delete();
            $this->syncChapters($projectBook, $validated['chapters'] ?? []);
        });

        return redirect()
            ->route('admin.project-books.index')
            ->with('success', 'Project book updated successfully.');
    }

    /* ------------------------------------------------------------------ */
    /* DESTROY                                                            */
    /* ------------------------------------------------------------------ */
    public function destroy(ProjectBook $projectBook)
    {
        DB::transaction(function () use ($projectBook) {
            $projectBook->chapters()->delete();
            $projectBook->delete();
        });

        return redirect()
            ->route('admin.project-books.index')
            ->with('success', 'Project book deleted successfully.');
    }

    /* ------------------------------------------------------------------ */
    /* Helper                                                             */
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
}