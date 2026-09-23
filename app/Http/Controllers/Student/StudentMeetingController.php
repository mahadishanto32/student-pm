<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\Project;
use Illuminate\Http\Request;

class StudentMeetingController extends Controller
{
    /**
     * Allowed ENUM values — keep in sync with the DB schema.
     */
    public const PLATFORMS = ['physical', 'online'];
    public const TYPES     = ['present', 'completed', 'upcoming'];

    /**
     * Return only the IDs of projects the logged-in student is a team member of.
     */
    protected function assignedProjectIds()
    {
        return Project::whereHas('teamMembers', function ($q) {
            $q->where('users.id', auth()->id());
        })->pluck('id');
    }

    /**
     * Ensure the current student belongs to the meeting's project.
     * Aborts with 403 if not allowed.
     */
    protected function authorizeMeeting(Meeting $meeting): void
    {
        $allowed = Project::where('id', $meeting->project_id)
            ->whereHas('teamMembers', function ($q) {
                $q->where('users.id', auth()->id());
            })
            ->exists();

        abort_unless($allowed, 403, 'You are not allowed to access this meeting.');
    }

    /**
     * Display a listing of meetings for the student's projects.
     */
    public function index(Request $request)
    {
        $projectIds = $this->assignedProjectIds();

        $query = Meeting::with('project')
            ->whereIn('project_id', $projectIds);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('supervisor_note', 'like', "%{$search}%")
                  ->orWhereHas('project', function ($pq) use ($search) {
                      $pq->where('project_name', 'like', "%{$search}%")
                         ->orWhere('group_number', 'like', "%{$search}%");
                  });
            });
        }

        if ($type = $request->input('type')) {
            if (in_array($type, self::TYPES, true)) {
                $query->where('type', $type);
            }
        }

        if ($platform = $request->input('platform')) {
            if (in_array($platform, self::PLATFORMS, true)) {
                $query->where('platform', $platform);
            }
        }

        if ($projectId = $request->input('project_id')) {
            if ($projectIds->contains((int) $projectId)) {
                $query->where('project_id', $projectId);
            }
        }

        $meetings = $query->orderByDesc('meeting_date_and_time')
                          ->paginate(15)
                          ->withQueryString();

        // Only projects the student belongs to appear in the filter dropdown
        $projects = Project::whereIn('id', $projectIds)
            ->orderBy('project_name')
            ->get();

        return view('student.meetings.index', [
            'meetings'  => $meetings,
            'projects'  => $projects,
            'types'     => self::TYPES,
            'platforms' => self::PLATFORMS,
        ]);
    }

    /**
     * Display the specified meeting (read-only).
     */
    public function show(Meeting $meeting)
    {
        $this->authorizeMeeting($meeting);

        $meeting->load(['project.teacher', 'project.teamMembers']);

        return view('student.meetings.show', compact('meeting'));
    }
}