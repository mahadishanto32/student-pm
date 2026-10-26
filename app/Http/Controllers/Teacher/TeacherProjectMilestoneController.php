<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectMilestone;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class TeacherProjectMilestoneController extends Controller
{
    protected array $allowedMimes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx'];

    /**
     * IDs of projects assigned to the logged-in teacher.
     */
    protected function assignedProjectIds(): array
    {
        return Project::where('assigned_teacher', Auth::id())
            ->pluck('id')
            ->toArray();
    }

    protected function statuses(): array
    {
        return [
            ProjectMilestone::STATUS_PENDING,
            ProjectMilestone::STATUS_NEED_CORRECTION,
            ProjectMilestone::STATUS_COMPLETED,
            ProjectMilestone::STATUS_REJECTED,
        ];
    }

    /**
     * Index — milestones from projects assigned to this teacher.
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

        return view('teachers.milestones.index', compact('milestones', 'projects'));
    }

    /**
     * Create form.
     */
    public function create(Request $request)
    {
        $projects = Project::whereIn('id', $this->assignedProjectIds())
            ->orderBy('group_number')
            ->get();

        if ($projects->isEmpty()) {
            return redirect()
                ->route('teachers.milestones.index')
                ->with('error', 'You are not assigned to any project.');
        }

        $selectedProjectId = $request->get('project_id');

        return view('teachers.milestones.create', compact('projects', 'selectedProjectId'));
    }

    /**
     * Store a milestone + its tasks.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id'              => ['required', Rule::in($this->assignedProjectIds())],
            'title'                   => ['required', 'string', 'max:255'],
            'tentative_time'          => ['nullable', 'date'],
            'status'                  => ['nullable', Rule::in($this->statuses())],
            'supervisor_note'         => ['nullable', 'string', 'max:5000'],

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
                'status'          => $validated['status'] ?? ProjectMilestone::STATUS_PENDING,
                'supervisor_note' => $validated['supervisor_note'] ?? null,
                'done_by'         => Auth::id(),
            ]);

            $this->syncTasks($milestone, $request);

            DB::commit();

            return redirect()
                ->route('teachers.milestones.show', $milestone->id)
                ->with('success', 'Milestone created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create milestone: ' . $e->getMessage());
        }
    }

    /**
     * Show a milestone belonging to one of the teacher's projects.
     */
    public function show(string $id)
    {
        $milestone = ProjectMilestone::with(['project', 'doneBy', 'tasks'])
            ->whereIn('project_id', $this->assignedProjectIds())
            ->findOrFail($id);

        return view('teachers.milestones.show', compact('milestone'));
    }

    /**
     * Edit form.
     */
    public function edit(string $id)
    {
        $milestone = ProjectMilestone::with('tasks')
            ->whereIn('project_id', $this->assignedProjectIds())
            ->findOrFail($id);

        $projects = Project::whereIn('id', $this->assignedProjectIds())
            ->orderBy('group_number')
            ->get();

        return view('teachers.milestones.edit', compact('milestone', 'projects'));
    }

    /**
     * Update everything: milestone fields, status, supervisor note, tasks.
     */
    public function update(Request $request, string $id)
    {
        $milestone = ProjectMilestone::with('tasks')
            ->whereIn('project_id', $this->assignedProjectIds())
            ->findOrFail($id);

        $validated = $request->validate([
            'project_id'              => ['required', Rule::in($this->assignedProjectIds())],
            'title'                   => ['required', 'string', 'max:255'],
            'tentative_time'          => ['nullable', 'date'],
            'status'                  => ['required', Rule::in($this->statuses())],
            'supervisor_note'         => ['nullable', 'string', 'max:5000'],

            'tasks'                   => ['required', 'array', 'min:1'],
            'tasks.*.id'              => ['nullable', 'integer'],
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
                'status'          => $validated['status'],
                'supervisor_note' => $validated['supervisor_note'] ?? null,
            ]);

            $this->syncTasks($milestone, $request);

            DB::commit();

            return redirect()
                ->route('teachers.milestones.show', $milestone->id)
                ->with('success', 'Milestone updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update milestone: ' . $e->getMessage());
        }
    }

    /**
     * Delete a milestone and its tasks/documents.
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
                ->route('teachers.milestones.index')
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
     * Sync tasks: update existing, create new, delete removed.
     * Works for both store (no ids) and update.
     */
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

            if (isset($files[$index]['document']) && $files[$index]['document'] instanceof UploadedFile) {
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