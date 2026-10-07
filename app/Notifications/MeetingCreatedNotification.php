<?php

namespace App\Notifications;

use App\Models\Meeting;
use Illuminate\Notifications\Notification;

class MeetingCreatedNotification extends Notification
{
    public function __construct(public Meeting $meeting) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $project = $this->meeting->project;

        return [
            'type'       => 'meeting_created',
            'title'      => 'New meeting',
            'message'    => "Meeting \"{$this->meeting->title}\" scheduled for "
                            . $this->meeting->meeting_date_and_time->format('d M Y, h:i A')
                            . " in project \"{$project->project_name}\".",
            'project_id' => $project->id,
            'meeting_id' => $this->meeting->id,
        ];
    }
}
