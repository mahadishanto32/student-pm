<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectBook;
use App\Models\ProjectMark;
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

        // project_id => ['marked' => n, 'avg' => x] for the "Project Book" topic
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

        return view('admin.project-book.index', compact('projectBooks', 'marksSummary'));
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

        $students = $projectBook->project->teamMembers
            ->where('role', 'student')
            ->values();

        $marks = ProjectMark::with([
                'distributions' => fn ($q) => $q->where('topic', 'Project Book'),
                'supervisor',
            ])
            ->where('project_id', $projectBook->project_id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        return view('admin.project-book.show', compact('projectBook', 'students', 'marks'));
    }



    /* ------------------------------------------------------------------ */
    /* DESTROY                                                            */
    /* ------------------------------------------------------------------ */
    public function destroy(ProjectBook $projectBook)
    {
        DB::transaction(function () use ($projectBook) {
            foreach ($projectBook->chapters as $chapter) {
                $this->deleteChapterFiles($chapter->documents ?? []);
            }
            $projectBook->chapters()->delete();
            $projectBook->delete();
        });

        return redirect()
            ->route('admin.project-books.index')
            ->with('success', 'Project book deleted successfully.');
    }

    protected function deleteChapterFiles(array $paths): void
    {
        $uploadDir = 'uploads/book-chapters';
        foreach ($paths as $path) {
            if (!str_starts_with($path, $uploadDir . '/')) {
                continue;
            }
            $full = public_path($path);
            if (\Illuminate\Support\Facades\File::exists($full)) {
                \Illuminate\Support\Facades\File::delete($full);
            }
        }
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