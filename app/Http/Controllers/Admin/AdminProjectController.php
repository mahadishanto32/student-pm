<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminProjectController extends Controller
{
    /**
     * List all projects with search + filter.
     */
    public function index(Request $request)
    {
        $query = Project::with(['teacher', 'teamMembers']);

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

        return view('admin.projects.index', compact('projects'));
    }

    /**
     * Show the create form.
     */
    public function create()
    {
        $teachers = User::where('role', 'teacher')->orderBy('name')->get();
        $members  = User::where('role', 'student')->orderBy('name')->get();

        return view('admin.projects.create', compact('teachers', 'members'));
    }

    /**
     * Store a new project.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'group_number'         => ['required', 'string', 'max:255', 'unique:projects,group_number'],
            'project_name'         => ['nullable', 'string', 'max:255'],
            'project_topic'        => ['nullable', 'string', 'max:255'],
            'short_overview'       => ['nullable', 'string'],
            'assigned_teacher'     => ['nullable', 'exists:users,id'],
            'assigned_team_member' => ['nullable', 'array'],
            'assigned_team_member.*' => ['exists:users,id'],
            'start_date'           => ['required', 'date'],
            'tentative_end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
            'status'               => ['required', Rule::in([
                'approved', 'pending', 'working', 'completed', 'rejected', 'cancelled',
            ])],
        ]);

        $members = $validated['assigned_team_member'] ?? [];
        unset($validated['assigned_team_member']);

        $project = Project::create($validated);

        if (!empty($members)) {
            $project->teamMembers()->sync($members);
        }

        return redirect()
            ->route('admin.projects.index')
            ->with('success', 'Project created successfully.');
    }

    /**
     * Show a single project.
     */
    public function show(Project $project)
    {
        $project->load(['teacher', 'teamMembers']);

        return view('admin.projects.show', compact('project'));
    }

    /**
     * Show the edit form.
     */
    public function edit(Project $project)
    {
        $project->load('teamMembers');

        $teachers = User::where('role', 'teacher')->orderBy('name')->get();
        $members  = User::where('role', 'student')->orderBy('name')->get();

        return view('admin.projects.edit', compact('project', 'teachers', 'members'));
    }

    /**
     * Update an existing project.
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'group_number'         => ['required', 'string', 'max:255', Rule::unique('projects', 'group_number')->ignore($project->id)],
            'project_name'         => ['nullable', 'string', 'max:255'],
            'project_topic'        => ['nullable', 'string', 'max:255'],
            'short_overview'       => ['nullable', 'string'],
            'assigned_teacher'     => ['nullable', 'exists:users,id'],
            'assigned_team_member' => ['nullable', 'array'],
            'assigned_team_member.*' => ['exists:users,id'],
            'start_date'           => ['required', 'date'],
            'tentative_end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
            'status'               => ['required', Rule::in([
                'approved', 'pending', 'working', 'completed', 'rejected', 'cancelled',
            ])],
        ]);

        $members = $validated['assigned_team_member'] ?? [];
        unset($validated['assigned_team_member']);

        $project->update($validated);
        $project->teamMembers()->sync($members);

        return redirect()
            ->route('admin.projects.index')
            ->with('success', 'Project updated successfully.');
    }

    /**
     * Delete a project.
     */
    public function destroy(Project $project)
    {
        $project->teamMembers()->detach();
        $project->delete();

        return redirect()
            ->route('admin.projects.index')
            ->with('success', 'Project deleted successfully.');
    }
}