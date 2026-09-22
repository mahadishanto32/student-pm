<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeacherProjectController extends Controller
{
    /**
     * List projects assigned to the logged-in teacher.
     */
    public function index(Request $request)
    {
        $query = Project::with(['teacher', 'teamMembers'])
                        ->where('assigned_teacher', auth()->id());

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('group_number', 'like', "%{$search}%")
                  ->orWhere('project_name', 'like', "%{$search}%")
                  ->orWhere('project_topic', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $projects = $query->latest()->paginate(10);

        return view('teachers.projects.index', compact('projects'));
    }

    /**
     * Show a single project — only if assigned to this teacher.
     */
    public function show(Project $project)
    {
        $this->authorizeProject($project);

        $project->load(['teacher', 'teamMembers']);

        return view('teachers.projects.show', compact('project'));
    }

    /**
     * Show the edit form — teacher can only edit limited fields.
     */
    public function edit(Project $project)
    {
        $this->authorizeProject($project);

        $project->load(['teacher', 'teamMembers']);

        return view('teachers.projects.edit', compact('project'));
    }

    /**
     * Update — only the teacher-editable fields.
     */
    public function update(Request $request, Project $project)
    {
        $this->authorizeProject($project);

        $validated = $request->validate([
            'project_name'       => ['nullable', 'string', 'max:255'],
            'project_topic'      => ['nullable', 'string', 'max:255'],
            'short_overview'     => ['nullable', 'string'],
            'start_date'         => ['required', 'date'],
            'tentative_end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status'             => ['required', Rule::in([
                'approved', 'pending', 'working', 'completed', 'rejected', 'cancelled',
            ])],
        ]);

        // Group number, assigned_teacher, team members are NOT touched.

        $project->update($validated);

        return redirect()
            ->route('teachers.projects.index')
            ->with('success', 'Project updated successfully.');
    }

    /**
     * Ensure the project belongs to the logged-in teacher.
     */
    private function authorizeProject(Project $project): void
    {
        if ((int) $project->assigned_teacher !== (int) auth()->id()) {
            abort(403, 'You are not assigned to this project.');
        }
    }
}