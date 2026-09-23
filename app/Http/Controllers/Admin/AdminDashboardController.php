<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectBook;
use App\Models\ProjectMilestone;
use App\Models\User;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // ----- Top counters -----
        $totalUsers        = User::count();
        $totalProjects     = Project::count();
        $totalProjectBooks = ProjectBook::count();
        $totalMilestones   = ProjectMilestone::count();

        // ----- Project book status breakdown (replaces the social-icons row) -----
        $projectBookStats = collect(ProjectBook::statuses())->mapWithKeys(
            fn ($status) => [$status => ProjectBook::where('status', $status)->count()]
        );

        // ----- Milestone status breakdown (progress bars) -----
        $milestoneStatuses = [
            ProjectMilestone::STATUS_PENDING,
            ProjectMilestone::STATUS_NEED_CORRECTION,
            ProjectMilestone::STATUS_COMPLETED,
            ProjectMilestone::STATUS_REJECTED,
        ];

        $milestoneStats = collect($milestoneStatuses)->map(function ($status) use ($totalMilestones) {
            $count   = ProjectMilestone::where('status', $status)->count();
            $percent = $totalMilestones > 0 ? (int) round(($count / $totalMilestones) * 100) : 0;

            return [
                'status'  => $status,
                'label'   => ucwords(str_replace('_', ' ', $status)),
                'count'   => $count,
                'percent' => $percent,
            ];
        });

        // ----- Projects created per month, last 7 months (for the area chart) -----
        $months = collect(range(6, 0))->map(fn ($i) => Carbon::now()->subMonths($i));

        $chartLabels = $months->map->format('M')->values()->toArray();

        $chartData = $months->map(function (Carbon $month) {
            return Project::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        })->values()->toArray();

        // ----- Recent projects (replaces the testimonial slider) -----
        $recentProjects = Project::with('teacher')
            ->latest()
            ->take(3)
            ->get();

        // ----- Today's milestones ("today tasks" box) -----
        $todaysMilestones = ProjectMilestone::with('project')
            ->whereDate('tentative_time', Carbon::today())
            ->orderBy('tentative_time')
            ->take(5)
            ->get();

        // ----- Recent milestone activity ("Updates" box) -----
        $recentUpdates = ProjectMilestone::with(['project', 'doneBy'])
            ->latest('updated_at')
            ->take(4)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalProjects',
            'totalProjectBooks',
            'totalMilestones',
            'projectBookStats',
            'milestoneStats',
            'chartLabels',
            'chartData',
            'recentProjects',
            'todaysMilestones',
            'recentUpdates',
        ));
    }
}