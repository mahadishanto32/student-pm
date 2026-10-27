<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Services\ProjectNotificationService;
use App\Models\PartnerApproval;
use App\Notifications\PartnerApprovalRequestNotification;
use Illuminate\Support\Facades\DB;

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

        $invitees = collect($validated['assigned_team_member'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->reject(fn ($id) => $id === auth()->id())
            ->unique();

        unset($validated['assigned_team_member']);
        $validated['status'] = 'pending';

        $project = DB::transaction(function () use ($validated, $invitees) {
            $project = Project::create($validated);

            // Only the creator is a member until the others approve
            $project->teamMembers()->sync([auth()->id()]);

            foreach ($invitees as $userId) {
                PartnerApproval::create([
                    'project_id'   => $project->id,
                    'user_id'      => $userId,
                    'invited_by'   => auth()->id(),
                    'approve'      => 'Pending',
                    'for_creation' => true,
                ]);
            }

            return $project;
        });

        // Send approval requests
        User::whereIn('id', $invitees)->get()->each(
            fn (User $u) => $u->notify(new PartnerApprovalRequestNotification($project, auth()->user()))
        );

        // No invitees -> nothing to wait for, notify admins right away
        if ($invitees->isEmpty()) {
            app(ProjectNotificationService::class)->projectCreated($project);
        }

        return redirect()
            ->route('student.projects.index')
            ->with('success', $invitees->isEmpty()
                ? 'Project created successfully. It is now pending approval.'
                : 'Project saved. Approval requests were sent to the selected members; the project is created once they all accept.');
    }

    /**
     * Show a single project — only if the student is a member.
     */
    public function show(Project $project)
    {
        $myApproval = $project->partnerApprovals()
            ->where('user_id', auth()->id())
            ->pending()
            ->first();

        if (! $myApproval) {
            $this->authorizeProject($project);
        }

        // Mark invitation notifications for this project as read
        auth()->user()->unreadNotifications
            ->filter(fn ($n) => ($n->data['project_id'] ?? null) === $project->id)
            ->each->markAsRead();

        $project->load(['teacher', 'teamMembers']);

        return view('student.projects.show', compact('project', 'myApproval'));
    }

    /**
     * Show the edit form — student can edit name, topic, overview, and team members.
     */
    public function edit(Project $project)
    {
        $this->authorizeProject($project);

        $project->load(['teacher', 'teamMembers']);

        $pendingIds = $project->partnerApprovals()->pending()->pluck('user_id')->toArray();

        $members = User::where('role', 'student')->orderBy('name')->get();

        return view('student.projects.edit', compact('project', 'members', 'pendingIds'));
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

        $requested = collect($validated['assigned_team_member'] ?? [])
            ->map(fn ($id) => (int) $id)->unique();
        unset($validated['assigned_team_member']);

        $current = $project->teamMembers()->pluck('users.id');
        $pending = $project->partnerApprovals()->pending()->pluck('user_id');

        $toRemove = $current->diff($requested);                  // existing members deselected
        $toCancel = $pending->diff($requested);                  // pending invites deselected
        $toInvite = $requested->diff($current)->diff($pending);  // brand-new (or re-invited)

        DB::transaction(function () use ($project, $validated, $toRemove, $toCancel, $toInvite) {
            $project->update($validated);

            if ($toRemove->isNotEmpty()) {
                $project->teamMembers()->detach($toRemove->all());
                $project->partnerApprovals()->whereIn('user_id', $toRemove)->delete();
            }

            if ($toCancel->isNotEmpty()) {
                $project->partnerApprovals()->whereIn('user_id', $toCancel)->delete();
            }

            foreach ($toInvite as $userId) {
                PartnerApproval::updateOrCreate(
                    ['project_id' => $project->id, 'user_id' => $userId],
                    [
                        'invited_by'   => auth()->id(),
                        'approve'      => 'Pending',
                        'for_creation' => false,
                    ]
                );
            }
        });

        User::whereIn('id', $toInvite)->get()->each(
            fn (User $u) => $u->notify(new PartnerApprovalRequestNotification($project, auth()->user()))
        );

        return redirect()
            ->route('student.projects.index')
            ->with('success', $toInvite->isEmpty()
                ? 'Project updated successfully.'
                : 'Project updated. New members will be added once they accept the invitation.');
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
