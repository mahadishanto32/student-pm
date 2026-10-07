<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HandlesNotifications;

class StudentNotificationController extends Controller
{
    use HandlesNotifications;

    protected function urlFor(array $data): string
    {
        // Adjust to your real route name for the project detail page
        return route('student.projects.show', $data['project_id']);
    }
}
