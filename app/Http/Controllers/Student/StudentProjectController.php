<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Validation\Rule;
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
     * Show the create form for students.
     */
    public function create()
    {
        // Students can only pick other students as team members
        $members = User::where('role', 'student')
                    ->where('id', '!=', auth()->id())
                    ->orderBy('name')
                    ->get();

        return view('student.projects.create', compact('members'));
    }

    /**
     * Store a new project created by a student.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_name'            => ['required', 'string', 'max:255'],
            'project_topic'           => ['nullable', 'string', 'max:255'],
            'short_overview'          => ['nullable', 'string'],
            'assigned_team_member'    => ['nullable', 'array'],
            'assigned_team_member.*'  => ['exists:users,id'],
            'start_date'              => ['required', 'date'],
            'tentative_end_date'      => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $members = $validated['assigned_team_member'] ?? [];
        unset($validated['assigned_team_member']);

        // Auto-set status to pending for student submissions
        $validated['status'] = 'pending';

        $project = Project::create($validated);

        // Always include the creating student as a team member
        $memberIds = array_unique(array_merge($members, [auth()->id()]));
        $project->teamMembers()->sync($memberIds);

        return redirect()
            ->route('student.projects.index')
            ->with('success', 'Project created successfully. It is now pending approval.');
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
     * Show the edit form — student can edit name, topic, overview, and team members.
     */
    public function edit(Project $project)
    {
        $this->authorizeProject($project);

        $project->load(['teacher', 'teamMembers']);

        // Students can only be team members
        $members = User::where('role', 'student')
                    ->orderBy('name')
                    ->get();

        return view('student.projects.edit', compact('project', 'members'));
    }

    /**
     * Update — name, topic, overview, and team members.
     */
    public function update(Request $request, Project $project)
    {
        $this->authorizeProject($project);

        $validated = $request->validate([
            'project_name'            => ['nullable', 'string', 'max:255'],
            'project_topic'           => ['nullable', 'string', 'max:255'],
            'short_overview'          => ['nullable', 'string'],
            'assigned_team_member'    => ['nullable', 'array'],
            'assigned_team_member.*'  => ['exists:users,id'],
        ]);

        $members = $validated['assigned_team_member'] ?? [];
        unset($validated['assigned_team_member']);

        $project->update($validated);

        // Sync team members — this adds AND removes members as needed
        $project->teamMembers()->sync($members);

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