<?php

namespace App\Notifications;

use App\Models\ProjectMilestone;
use Illuminate\Notifications\Notification;

class MilestoneCreatedNotification extends Notification
{
    public function __construct(
        public ProjectMilestone $milestone,
        public string $teacherName,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $project = $this->milestone->project;

        return [
            'type'         => 'milestone_created',
            'title'        => 'New milestone',
            'message'      => "{$this->teacherName} created milestone \"{$this->milestone->title}\" for project \"{$project->project_name}\".",
            'project_id'   => $project->id,
            'milestone_id' => $this->milestone->id,
            'url'          => route('student.milestones.show', $this->milestone->id),
        ];
    }
}