<?php

namespace App\Observers;

use App\Models\Meeting;
use App\Services\ProjectNotificationService;

class MeetingObserver
{
    public function created(Meeting $meeting): void
    {
        app(ProjectNotificationService::class)->meetingCreated($meeting);
    }
}
