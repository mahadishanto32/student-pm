<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\ProjectMark;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class StudentProjectMilestoneController extends Controller
{
    protected array $allowedMimes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx'];

    /**
     * Only projects the current student is assigned to.
     */
    protected function assignedProjectIds(): array
    {
        return Auth::user()
            ->projects()
            ->pluck('projects.id')
            ->toArray();
    }

    /**
     * Index — list milestones from all assigned projects.
     */
    public function index(Request $request)
    {
        $projectIds = $this->assignedProjectIds();

        $milestones = ProjectMilestone::with(['project', 'doneBy', 'tasks'])
            ->whereIn('project_id', $projectIds)
            ->when($request->filled('project_id'), fn ($q) =>
                $q->where('project_id', $request->project_id))
            ->when($request->filled('status'), fn ($q) =>
                $q->where('status', $request->status))
            ->when($request->filled('search'), fn ($q) =>
                $q->where('title', 'like', '%' . $request->search . '%'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $projects = Project::whereIn('id', $projectIds)
            ->orderBy('group_number')
            ->get();

        // project_id => ProjectMark (with the "Milestones" distribution) for this student only
        $myMarks = ProjectMark::with(['distributions' => fn ($q) => $q->where('topic', 'Milestones')])
            ->where('student_id', Auth::id())
            ->whereIn('project_id', $projectIds)
            ->get()
            ->keyBy('project_id');

        return view('student.milestones.index', compact('milestones', 'projects', 'myMarks'));
    }

    /**
     * Show a milestone with its tasks.
     */
    public function show(string $id)
    {
        $milestone = ProjectMilestone::with(['project', 'doneBy', 'tasks'])
            ->whereIn('project_id', $this->assignedProjectIds())
            ->findOrFail($id);

        Auth::user()->unreadNotifications
            ->filter(fn ($n) => ($n->data['milestone_id'] ?? null) === $milestone->id)
            ->each->markAsRead();

        $myMark = ProjectMark::with(['distributions' => fn ($q) => $q->where('topic', 'Milestones')])
            ->where('project_id', $milestone->project_id)
            ->where('student_id', Auth::id())
            ->first();

        return view('student.milestones.show', compact('milestone', 'myMark'));
    }

    /**
     * Show edit form.
     */
    public function edit(string $id)
    {
        $milestone = ProjectMilestone::with('tasks')
            ->whereIn('project_id', $this->assignedProjectIds())
            ->findOrFail($id);

        if ($milestone->status === ProjectMilestone::STATUS_COMPLETED) {
            return redirect()
                ->route('student.milestones.show', $milestone->id)
                ->with('error', 'Completed milestones cannot be edited.');
        }

        $projects = Project::whereIn('id', $this->assignedProjectIds())
            ->orderBy('group_number')
            ->get();

        return view('student.milestones.edit', compact('milestone', 'projects'));
    }

    /**
     * Update a milestone + its tasks.
     * Students cannot change project, status or supervisor_note.
     */
    public function update(Request $request, string $id)
    {
        $milestone = ProjectMilestone::with('tasks')
            ->whereIn('project_id', $this->assignedProjectIds())
            ->findOrFail($id);

        if ($milestone->status === ProjectMilestone::STATUS_COMPLETED) {
            return redirect()
                ->route('student.milestones.show', $milestone->id)
                ->with('error', 'Completed milestones cannot be edited.');
        }

        // Students can only update task descriptions and documents.
        $validated = $request->validate([
            'tasks'                   => ['required', 'array', 'min:1'],
            'tasks.*.id'              => ['required', 'integer'],
            'tasks.*.description'     => ['nullable', 'string', 'max:5000'],
            'tasks.*.document'        => ['nullable', 'file', 'mimes:' . implode(',', $this->allowedMimes), 'max:5120'],
            'tasks.*.remove_document' => ['nullable', 'boolean'],
        ]);

        DB::beginTransaction();

        try {
            $this->syncTasks($milestone, $request);

            DB::commit();

            return redirect()
                ->route('student.milestones.show', $milestone->id)
                ->with('success', 'Tasks updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update tasks: ' . $e->getMessage());
        }
    }

    /* ------------------------------------------------------------------ */
    /* Helpers                                                             */
    /* ------------------------------------------------------------------ */

    /**
     * Sync tasks: update existing, create new, delete removed.
     */
    protected function syncTasks(ProjectMilestone $milestone, Request $request): void
    {
        $rows  = $request->input('tasks', []);
        $files = $request->file('tasks', []);

        foreach ($rows as $index => $row) {
            if (empty($row['id'])) {
                continue; // students cannot add new tasks
            }

            // Scoped to this milestone so tasks from other milestones can't be touched.
            $task = $milestone->tasks()->where('id', $row['id'])->first();
            if (!$task) {
                continue;
            }

            $documentPath = $task->document;

            if (!empty($row['remove_document'])) {
                $this->deleteDocument($documentPath);
                $documentPath = null;
            }

            if (isset($files[$index]['document']) && $files[$index]['document'] instanceof UploadedFile) {
                $this->deleteDocument($documentPath);
                $documentPath = $this->uploadDocument($files[$index]['document']);
            }

            $task->update([
                'description' => $row['description'] ?? null,
                'document'    => $documentPath,
            ]);
        }
    }

    protected function uploadDocument(UploadedFile $file): string
    {
        $folder = public_path('uploads/tasks');

        if (!File::isDirectory($folder)) {
            File::makeDirectory($folder, 0755, true, true);
        }

        $name = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($folder, $name);

        return 'uploads/tasks/' . $name;
    }

    protected function deleteDocument(?string $relativePath): void
    {
        if (!$relativePath) {
            return;
        }

        $full = public_path($relativePath);

        if (File::exists($full)) {
            File::delete($full);
        }
    }
}