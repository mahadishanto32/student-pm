<?php

namespace App\Notifications;

use App\Models\Project;
use Illuminate\Notifications\Notification;

class ProjectCreatedNotification extends Notification
{
    public function __construct(public Project $project) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'       => 'project_created',
            'title'      => 'New project',
            'message'    => "Project \"{$this->project->project_name}\" has been created.",
            'project_id' => $this->project->id,
        ];
    }
}
