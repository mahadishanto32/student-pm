<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HandlesNotifications;

class AdminNotificationController extends Controller
{
    use HandlesNotifications;

    protected function urlFor(array $data): string
    {
        // Adjust to your real route name for the project detail page
        return route('admin.projects.show', $data['project_id']);
    }
}
