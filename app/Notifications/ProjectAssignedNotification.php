<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Notifications\Notification;

class ProjectAssignedNotification extends Notification
{
    public function __construct(public Project $project) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'       => 'project_assigned',
            'title'      => 'Project assigned to you',
            'message'    => "You have been assigned to project \"{$this->project->project_name}\".",
            'project_id' => $this->project->id,
        ];
    }
}
