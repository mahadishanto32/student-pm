<?php

namespace App\Services;

use App\Models\Meeting;
use App\Models\Project;
use App\Models\User;
use App\Notifications\MeetingCreatedNotification;
use App\Notifications\ProjectCreatedNotification;
use Illuminate\Support\Facades\Notification;

class ProjectNotificationService
{
    /** Admins + assigned teacher + assigned students. */
    public function projectCreated(Project $project): void
    {
        $recipients = User::where('role', 'admin')->get()
            ->merge($this->projectUsers($project))
            ->unique('id');

        Notification::send($recipients, new ProjectCreatedNotification($project));
    }

    /** Assigned teacher + assigned students (merge admins here if they should get these too). */
    public function meetingCreated(Meeting $meeting): void
    {
        $project = $meeting->project;

        if (! $project) {
            return;
        }

        Notification::send($this->projectUsers($project), new MeetingCreatedNotification($meeting));
    }

    private function projectUsers(Project $project)
    {
        $users = $project->teamMembers()->get();

        if ($project->assigned_teacher && $teacher = User::find($project->assigned_teacher)) {
            $users->push($teacher);
        }

        return $users->unique('id');
    }
}
