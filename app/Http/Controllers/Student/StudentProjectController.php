<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class StudentProjectController extends Controller
{
    /**
     * List projects where the logged-in student is a team member.
     */
    public function index(Request $request)
    {
        $userId = auth()->id();

        $query = Project::with(['teacher', 'teamMembers'])
                        ->whereHas('teamMembers', fn ($q) => $q->where('users.id', $userId));

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

        return view('student.projects.index', compact('projects'));
    }

    /**
     * Show a single project — only if the student is a member.
     */
    public function show(Project $project)
    {
        $this->authorizeProject($project);

        $project->load(['teacher', 'teamMembers']);

        return view('student.projects.show', compact('project'));
    }

    /**
     * Show the edit form — student can only edit name, topic, overview.
     */
    public function edit(Project $project)
    {
        $this->authorizeProject($project);

        $project->load(['teacher', 'teamMembers']);

        return view('student.projects.edit', compact('project'));
    }

    /**
     * Update — only project_name, project_topic, short_overview.
     */
    public function update(Request $request, Project $project)
    {
        $this->authorizeProject($project);

        $validated = $request->validate([
            'project_name'   => ['nullable', 'string', 'max:255'],
            'project_topic'  => ['nullable', 'string', 'max:255'],
            'short_overview' => ['nullable', 'string'],
        ]);

        $project->update($validated);

        return redirect()
            ->route('student.projects.index')
            ->with('success', 'Project updated successfully.');
    }

    /**
     * Ensure the logged-in student is a team member of the project.
     */
    private function authorizeProject(Project $project): void
    {
        $isMember = $project->teamMembers()
                            ->where('users.id', auth()->id())
                            ->exists();

        if (! $isMember) {
            abort(403, 'You are not a member of this project.');
        }
    }
}