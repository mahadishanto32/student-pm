<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class AdminProjectMilestoneController extends Controller
{
    /**
     * Allowed document mime extensions.
     */
    protected array $allowedMimes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx'];

    /**
     * Valid status values.
     */
    protected array $statuses = [
        'pending',
        'need_correction',
        'completed',
        'rejected',
    ];

    /**
     * Index — list all milestones across all projects.
     */
    public function index(Request $request)
    {
        $milestones = ProjectMilestone::with(['project', 'doneBy', 'tasks'])
            ->when($request->filled('project_id'), fn ($q) =>
                $q->where('project_id', $request->project_id))
            ->when($request->filled('status'), fn ($q) =>
                $q->where('status', $request->status))
            ->when($request->filled('search'), fn ($q) =>
                $q->where('title', 'like', '%' . $request->search . '%'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $projects = Project::orderBy('group_number')->get();

        return view('admin.milestones.index', compact('milestones', 'projects'));
    }

    /**
     * Show create form.
     */
    public function create(Request $request)
    {
        $projects = Project::orderBy('group_number')->get();
        $users    = User::orderBy('name')->get();

        $selectedProjectId = $request->get('project_id');

        return view('admin.milestones.create', compact('projects', 'users', 'selectedProjectId'));
    }

    /**
     * Store a milestone + its tasks.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id'              => ['required', 'exists:projects,id'],
            'title'                   => ['required', 'string', 'max:255'],
            'tentative_time'          => ['nullable', 'date'],
            'supervisor_note'         => ['nullable', 'string', 'max:5000'],
            'status'                  => ['required', Rule::in($this->statuses)],
            'done_by'                 => ['required', 'exists:users,id'],

            'tasks'                   => ['required', 'array', 'min:1'],
            'tasks.*.key_points'      => ['required', 'string', 'max:1000'],
            'tasks.*.document'        => ['nullable', 'file', 'mimes:' . implode(',', $this->allowedMimes), 'max:5120'],
        ]);

        DB::beginTransaction();

        try {
            $milestone = ProjectMilestone::create([
                'project_id'      => $validated['project_id'],
                'title'           => $validated['title'],
                'tentative_time'  => $validated['tentative_time'] ?? null,
                'supervisor_note' => $validated['supervisor_note'] ?? null,
                'status'          => $validated['status'],
                'done_by'         => $validated['done_by'],
            ]);

            $this->saveTasks($milestone, $request->input('tasks', []), $request->file('tasks', []));

            DB::commit();

            return redirect()
                ->route('admin.milestones.show', $milestone->id)
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
            ->findOrFail($id);

        return view('admin.milestones.show', compact('milestone'));
    }

    /**
     * Show edit form.
     */
    public function edit(string $id)
    {
        $milestone = ProjectMilestone::with('tasks')->findOrFail($id);

        $projects = Project::orderBy('group_number')->get();
        $users    = User::orderBy('name')->get();

        return view('admin.milestones.edit', compact('milestone', 'projects', 'users'));
    }

    /**
     * Update a milestone + its tasks.
     */
    public function update(Request $request, string $id)
    {
        $milestone = ProjectMilestone::with('tasks')->findOrFail($id);

        $validated = $request->validate([
            'project_id'              => ['required', 'exists:projects,id'],
            'title'                   => ['required', 'string', 'max:255'],
            'tentative_time'          => ['nullable', 'date'],
            'supervisor_note'         => ['nullable', 'string', 'max:5000'],
            'status'                  => ['required', Rule::in($this->statuses)],
            'done_by'                 => ['required', 'exists:users,id'],

            'tasks'                   => ['required', 'array', 'min:1'],
            'tasks.*.id'              => ['nullable', 'integer', 'exists:milestone_tasks,id'],
            'tasks.*.key_points'      => ['required', 'string', 'max:1000'],
            'tasks.*.document'        => ['nullable', 'file', 'mimes:' . implode(',', $this->allowedMimes), 'max:5120'],
            'tasks.*.remove_document' => ['nullable', 'boolean'],
        ]);

        DB::beginTransaction();

        try {
            $milestone->update([
                'project_id'      => $validated['project_id'],
                'title'           => $validated['title'],
                'tentative_time'  => $validated['tentative_time'] ?? null,
                'supervisor_note' => $validated['supervisor_note'] ?? null,
                'status'          => $validated['status'],
                'done_by'         => $validated['done_by'],
            ]);

            $this->syncTasks($milestone, $request);

            DB::commit();

            return redirect()
                ->route('admin.milestones.show', $milestone->id)
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
        $milestone = ProjectMilestone::with('tasks')->findOrFail($id);

        DB::beginTransaction();

        try {
            foreach ($milestone->tasks as $task) {
                $this->deleteDocument($task->document);
            }

            $milestone->tasks()->delete();
            $milestone->delete();

            DB::commit();

            return redirect()
                ->route('admin.milestones.index')
                ->with('success', 'Milestone deleted successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete milestone: ' . $e->getMessage());
        }
    }

    /* ------------------------------------------------------------------ */
    /* Helpers                                                             */
    /* ------------------------------------------------------------------ */

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

    protected function syncTasks(ProjectMilestone $milestone, Request $request): void
    {
        $rows        = $request->input('tasks', []);
        $files       = $request->file('tasks', []);
        $keptTaskIds = [];

        foreach ($rows as $index => $row) {
            $task = null;

            if (!empty($row['id'])) {
                $task = $milestone->tasks()->where('id', $row['id'])->first();
            }

            $documentPath = $task->document ?? null;

            if (!empty($row['remove_document'])) {
                $this->deleteDocument($documentPath);
                $documentPath = null;
            }

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

        $removed = $milestone->tasks()->whereNotIn('id', $keptTaskIds)->get();
        foreach ($removed as $task) {
            $this->deleteDocument($task->document);
            $task->delete();
        }
    }

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