<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use App\Support\DashboardMetrics;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $metrics = new DashboardMetrics(); // null scope = whole system

        $stats          = $metrics->counters();
        $stats['users'] = User::count();

        $roles = User::selectRaw('role, COUNT(*) AS c')->groupBy('role')->pluck('c', 'role');

        // Supervisor workload: projects per teacher (top 5)
        $workload = DB::table('projects')
            ->join('users', 'users.id', '=', 'projects.assigned_teacher')
            ->selectRaw('users.name, COUNT(*) AS projects')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('projects')
            ->take(5)->get();

        return view('admin.dashboard', [
            'stats'          => $stats,
            'roles'          => $roles,
            'unassigned'     => Project::whereNull('assigned_teacher')->count(),
            'submissions'    => [
                'presentations' => DB::table('presentations')->count(),
                'videos'        => DB::table('video_resumes')->count(),
                'meetings'      => DB::table('meetings')->count(),
            ],
            'workload'       => $workload,
            'projectStatus'  => $metrics->projectStatusStats(),
            'bookStats'      => $metrics->bookStats(),
            'milestoneStats' => $metrics->milestoneStats(),
            'chart'          => $metrics->monthlyActivity(withProjects: true),
            'projects'       => $metrics->projectCards(5),
            'attention'      => $metrics->attention(5),
            'updates'        => $metrics->recentUpdates(5),
            'schedule'       => $metrics->schedule(5),
            'notifications'  => DashboardMetrics::notifications(Auth::user()),
        ]);
    }
}
