<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectMilestone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class TeacherProjectMilestoneController extends Controller
{
    /**
     * Allowed document mime extensions (for replacing a document).
     */
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
     * Edit form — teacher can only edit status & supervisor_note.
     */
    public function edit(string $id)
    {
        $milestone = ProjectMilestone::with('tasks')
            ->whereIn('project_id', $this->assignedProjectIds())
            ->findOrFail($id);

        return view('teachers.milestones.edit', compact('milestone'));
    }

    /**
     * Update — teacher may update ONLY status & supervisor_note.
     */
    public function update(Request $request, string $id)
    {
        $milestone = ProjectMilestone::with('tasks')
            ->whereIn('project_id', $this->assignedProjectIds())
            ->findOrFail($id);

        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    ProjectMilestone::STATUS_PENDING,
                    ProjectMilestone::STATUS_NEED_CORRECTION,
                    ProjectMilestone::STATUS_COMPLETED,
                    ProjectMilestone::STATUS_REJECTED,
                ]),
            ],
            'supervisor_note' => ['nullable', 'string', 'max:5000'],
        ]);

        // Only these two fields are updated by the teacher.
        $milestone->update([
            'status'          => $validated['status'],
            'supervisor_note' => $validated['supervisor_note'] ?? null,
        ]);

        return redirect()
            ->route('teachers.milestones.show', $milestone->id)
            ->with('success', 'Milestone status updated successfully.');
    }

    /**
     * Delete a milestone and its tasks + documents.
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

    /**
     * Delete a document from public/ safely.
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