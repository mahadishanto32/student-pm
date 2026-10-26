<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectMilestone;
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

        return view('student.milestones.index', compact('milestones', 'projects'));
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

        $validated = $request->validate([
            'title'                   => ['required', 'string', 'max:255'],
            'tentative_time'          => ['nullable', 'date'],

            'tasks'                   => ['required', 'array', 'min:1'],
            'tasks.*.id'              => ['nullable', 'integer'],
            'tasks.*.key_points'      => ['required', 'string', 'max:1000'],
            'tasks.*.document'        => ['nullable', 'file', 'mimes:' . implode(',', $this->allowedMimes), 'max:5120'],
            'tasks.*.remove_document' => ['nullable', 'boolean'],
        ]);

        DB::beginTransaction();

        try {
            $milestone->update([
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

    /* ------------------------------------------------------------------ */
    /* Helpers                                                             */
    /* ------------------------------------------------------------------ */

    /**
     * Sync tasks: update existing, create new, delete removed.
     */
    protected function syncTasks(ProjectMilestone $milestone, Request $request): void
    {
        $rows        = $request->input('tasks', []);
        $files       = $request->file('tasks', []);
        $keptTaskIds = [];

        foreach ($rows as $index => $row) {
            $task = null;

            if (!empty($row['id'])) {
                // Scoped to this milestone, so tasks of other milestones can't be touched.
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