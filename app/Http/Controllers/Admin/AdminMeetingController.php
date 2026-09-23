<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminMeetingController extends Controller
{
    /**
     * Allowed ENUM values — keep in sync with the DB schema.
     */
    public const PLATFORMS = ['physical', 'online'];
    public const TYPES     = ['present', 'completed', 'upcoming'];

    /**
     * Display a listing of meetings.
     */
    public function index(Request $request)
    {
        $query = Meeting::with('project');

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
            $query->where('project_id', $projectId);
        }

        $meetings = $query->orderByDesc('meeting_date_and_time')
                          ->paginate(15)
                          ->withQueryString();

        $projects = Project::orderBy('project_name')->get();

        return view('admin.meetings.index', [
            'meetings'  => $meetings,
            'projects'  => $projects,
            'types'     => self::TYPES,
            'platforms' => self::PLATFORMS,
        ]);
    }

    /**
     * Show the form for creating a new meeting.
     */
    public function create()
    {
        $projects = Project::orderBy('project_name')->get();

        return view('admin.meetings.create', [
            'projects'  => $projects,
            'types'     => self::TYPES,
            'platforms' => self::PLATFORMS,
        ]);
    }

    /**
     * Store a newly created meeting.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id'                           => ['required', 'exists:projects,id'],
            'meeting_date_and_time'                => ['required', 'date'],
            'title'                                => ['required', 'string', 'max:255'],
            'description'                          => ['nullable', 'string'],
            'supervisor_note'                      => ['nullable', 'string'],
            'platform'                             => ['required', Rule::in(self::PLATFORMS)],
            'type'                                 => ['required', Rule::in(self::TYPES)],
            'tentative_next_meeting_date_and_time' => ['nullable', 'date', 'after:meeting_date_and_time'],
        ]);

        Meeting::create($validated);

        return redirect()
            ->route('admin.meetings.index')
            ->with('success', 'Meeting created successfully.');
    }

    /**
     * Display the specified meeting.
     */
    public function show(Meeting $meeting)
    {
        $meeting->load('project');

        return view('admin.meetings.show', compact('meeting'));
    }

    /**
     * Show the form for editing the specified meeting.
     */
    public function edit(Meeting $meeting)
    {
        $projects = Project::orderBy('project_name')->get();

        return view('admin.meetings.edit', [
            'meeting'   => $meeting,
            'projects'  => $projects,
            'types'     => self::TYPES,
            'platforms' => self::PLATFORMS,
        ]);
    }

    /**
     * Update the specified meeting.
     */
    public function update(Request $request, Meeting $meeting)
    {
        $validated = $request->validate([
            'project_id'                           => ['required', 'exists:projects,id'],
            'meeting_date_and_time'                => ['required', 'date'],
            'title'                                => ['required', 'string', 'max:255'],
            'description'                          => ['nullable', 'string'],
            'supervisor_note'                      => ['nullable', 'string'],
            'platform'                             => ['required', Rule::in(self::PLATFORMS)],
            'type'                                 => ['required', Rule::in(self::TYPES)],
            'tentative_next_meeting_date_and_time' => ['nullable', 'date', 'after:meeting_date_and_time'],
        ]);

        $meeting->update($validated);

        return redirect()
            ->route('admin.meetings.index')
            ->with('success', 'Meeting updated successfully.');
    }

    /**
     * Remove the specified meeting.
     */
    public function destroy(Meeting $meeting)
    {
        $meeting->delete();

        return redirect()
            ->route('admin.meetings.index')
            ->with('success', 'Meeting deleted successfully.');
    }
}