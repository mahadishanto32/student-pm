<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class PartnerApprovalResponseNotification extends Notification
{
    public function __construct(
        public string $projectName,
        public string $responderName,
        public string $answer,          // 'Yes' | 'No'
        public ?int $projectId = null,  // null if the project was deleted
        public bool $projectDeleted = false,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $accepted = $this->answer === 'Yes';

        $message = "{$this->responderName} " . ($accepted ? 'accepted' : 'declined')
                 . " the invitation for \"{$this->projectName}\".";

        if ($this->projectDeleted) {
            $message .= ' The project was not created.';
        }

        return [
            'type'       => 'partner_approval_response',
            'title'      => $accepted ? 'Invitation accepted' : 'Invitation declined',
            'message'    => $message,
            'project_id' => $this->projectId,
        ];
    }
}