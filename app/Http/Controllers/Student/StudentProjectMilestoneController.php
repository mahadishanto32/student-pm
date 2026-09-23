<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\MilestoneTask;
use App\Models\Project;
use App\Models\ProjectMilestone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class StudentProjectMilestoneController extends Controller
{
    /**
     * Allowed document mime extensions.
     */
    protected array $allowedMimes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx'];

    /**
     * Only return projects the current student is assigned to.
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

        return view('student.milestones.index', compact('milestones', 'projects'));
    }

    /**
     * Show create form.
     */
    public function create(Request $request)
    {
        $projects = Project::whereIn('id', $this->assignedProjectIds())
            ->orderBy('group_number')
            ->get();

        if ($projects->isEmpty()) {
            return redirect()
                ->route('student.milestones.index')
                ->with('error', 'You are not assigned to any project.');
        }

        $selectedProjectId = $request->get('project_id');

        return view('student.milestones.create', compact('projects', 'selectedProjectId'));
    }

    /**
     * Store a milestone + its tasks.
     */
    public function store(Request $request)
    {
        $projectIds = $this->assignedProjectIds();

        $validated = $request->validate([
            'project_id'              => ['required', Rule::in($projectIds)],
            'title'                   => ['required', 'string', 'max:255'],
            'tentative_time'          => ['nullable', 'date'],

            'tasks'                   => ['required', 'array', 'min:1'],
            'tasks.*.key_points'      => ['required', 'string', 'max:1000'],
            'tasks.*.document'        => ['nullable', 'file', 'mimes:' . implode(',', $this->allowedMimes), 'max:5120'],
        ]);

        DB::beginTransaction();

        try {
            $milestone = ProjectMilestone::create([
                'project_id'     => $validated['project_id'],
                'title'          => $validated['title'],
                'tentative_time' => $validated['tentative_time'] ?? null,
                'status'         => ProjectMilestone::STATUS_PENDING,
                'done_by'        => Auth::id(),
            ]);

            $this->saveTasks($milestone, $request->input('tasks', []), $request->file('tasks', []));

            DB::commit();

            return redirect()
                ->route('student.milestones.show', $milestone->id)
                ->with('success', 'Milestone created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create milestone: ' . $e->getMessage());
        }
    }

    /**
     * Show a milestone with its tasks.
     */
    public function show(string $id)
    {
        $milestone = ProjectMilestone::with(['project', 'doneBy', 'tasks'])
            ->whereIn('project_id', $this->assignedProjectIds())
            ->findOrFail($id);

        return view('student.milestones.show', compact('milestone'));
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

        $projectIds = $this->assignedProjectIds();

        $validated = $request->validate([
            'project_id'              => ['required', Rule::in($projectIds)],
            'title'                   => ['required', 'string', 'max:255'],
            'tentative_time'          => ['nullable', 'date'],

            'tasks'                   => ['required', 'array', 'min:1'],
            'tasks.*.id'              => ['nullable', 'integer', 'exists:milestone_tasks,id'],
            'tasks.*.key_points'      => ['required', 'string', 'max:1000'],
            'tasks.*.document'        => ['nullable', 'file', 'mimes:' . implode(',', $this->allowedMimes), 'max:5120'],
            'tasks.*.remove_document' => ['nullable', 'boolean'],
        ]);

        DB::beginTransaction();

        try {
            // NOTE: status and supervisor_note are intentionally NOT updated here.
            $milestone->update([
                'project_id'     => $validated['project_id'],
                'title'          => $validated['title'],
                'tentative_time' => $validated['tentative_time'] ?? null,
            ]);

            $this->syncTasks($milestone, $request);

            DB::commit();

            return redirect()
                ->route('student.milestones.show', $milestone->id)
                ->with('success', 'Milestone updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update milestone: ' . $e->getMessage());
        }
    }

    /**
     * Delete a milestone (and its tasks + documents).
     */
    public function destroy(string $id)
    {
        $milestone = ProjectMilestone::with('tasks')
            ->whereIn('project_id', $this->assignedProjectIds())
            ->findOrFail($id);

        DB::beginTransaction();

        try {
            foreach ($milestone->tasks as $task) {
                $this->deleteDocument($task->document);
            }

            $milestone->tasks()->delete();
            $milestone->delete();

            DB::commit();

            return redirect()
                ->route('student.milestones.index')
                ->with('success', 'Milestone deleted successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete milestone: ' . $e->getMessage());
        }
    }

    /* ------------------------------------------------------------------ */
    /* Helpers                                                             */
    /* ------------------------------------------------------------------ */

    /**
     * Persist tasks for a newly created milestone.
     */
    protected function saveTasks(ProjectMilestone $milestone, array $rows, array $files): void
    {
        foreach ($rows as $index => $row) {
            $documentPath = null;

            if (isset($files[$index]['document']) && $files[$index]['document'] instanceof \Illuminate\Http\UploadedFile) {
                $documentPath = $this->uploadDocument($files[$index]['document']);
            }

            $milestone->tasks()->create([
                'key_points' => $row['key_points'],
                'document'   => $documentPath,
            ]);
        }
    }

    /**
     * Sync tasks on update: update existing, create new, delete removed.
     */
    protected function syncTasks(ProjectMilestone $milestone, Request $request): void
    {
        $rows       = $request->input('tasks', []);
        $files      = $request->file('tasks', []);
        $keptTaskIds = [];

        foreach ($rows as $index => $row) {
            $task = null;

            if (!empty($row['id'])) {
                $task = $milestone->tasks()->where('id', $row['id'])->first();
            }

            $documentPath = $task->document ?? null;

            // Handle explicit removal
            if (!empty($row['remove_document'])) {
                $this->deleteDocument($documentPath);
                $documentPath = null;
            }

            // Handle new upload
            if (isset($files[$index]['document']) && $files[$index]['document'] instanceof \Illuminate\Http\UploadedFile) {
                $this->deleteDocument($documentPath);
                $documentPath = $this->uploadDocument($files[$index]['document']);
            }

            if ($task) {
                $task->update([
                    'key_points' => $row['key_points'],
                    'document'   => $documentPath,
                ]);
                $keptTaskIds[] = $task->id;
            } else {
                $newTask = $milestone->tasks()->create([
                    'key_points' => $row['key_points'],
                    'document'   => $documentPath,
                ]);
                $keptTaskIds[] = $newTask->id;
            }
        }

        // Delete tasks removed from the form
        $removed = $milestone->tasks()->whereNotIn('id', $keptTaskIds)->get();
        foreach ($removed as $task) {
            $this->deleteDocument($task->document);
            $task->delete();
        }
    }

    /**
     * Save an uploaded document to public/uploads/tasks and return its relative path.
     */
    protected function uploadDocument(\Illuminate\Http\UploadedFile $file): string
    {
        $folder = public_path('uploads/tasks');

        if (!File::isDirectory($folder)) {
            File::makeDirectory($folder, 0755, true, true);
        }

        $name = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($folder, $name);

        return 'uploads/tasks/' . $name;
    }

    /**
     * Delete a document from disk (safely).
     */
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