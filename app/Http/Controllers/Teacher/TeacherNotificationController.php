<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HandlesNotifications;

class TeacherNotificationController extends Controller
{
    use HandlesNotifications;

    protected function urlFor(array $data): string
    {
        return route('teachers.projects.show', $data['project_id']);
    }
}
