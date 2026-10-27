<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\User;
use Illuminate\Notifications\Notification;

class PartnerApprovalRequestNotification extends Notification
{
    public function __construct(public Project $project, public User $inviter) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type'       => 'partner_approval_request',
            'title'      => 'Team invitation',
            'message'    => "{$this->inviter->name} assigned you to project \"{$this->project->project_name}\". Please accept or decline.",
            'project_id' => $this->project->id,
        ];
    }
}