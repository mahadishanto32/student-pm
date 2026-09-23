<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ProjectBook;
use App\Models\ProjectMilestone;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $student = Auth::user();

        // Base query: only the project(s) this student is a team member of.
        $projectIds = $student->projects()->pluck('projects.id');

        // ----- Top counters (scoped to this student's projects) -----
        $totalProjects     = $projectIds->count();
        $totalProjectBooks = ProjectBook::whereIn('project_id', $projectIds)->count();
        $totalMilestones   = ProjectMilestone::whereIn('project_id', $projectIds)->count();
        $pendingMilestones = ProjectMilestone::whereIn('project_id', $projectIds)
            ->where('status', ProjectMilestone::STATUS_PENDING)
            ->count();

        // ----- Project book status breakdown (replaces the social-icons row) -----
        $projectBookStats = collect(ProjectBook::statuses())->mapWithKeys(
            fn ($status) => [
                $status => ProjectBook::whereIn('project_id', $projectIds)
                    ->where('status', $status)
                    ->count(),
            ]
        );

        // ----- Milestone status breakdown (progress bars) -----
        $milestoneStatuses = [
            ProjectMilestone::STATUS_PENDING,
            ProjectMilestone::STATUS_NEED_CORRECTION,
            ProjectMilestone::STATUS_COMPLETED,
            ProjectMilestone::STATUS_REJECTED,
        ];

        $milestoneStats = collect($milestoneStatuses)->map(function ($status) use ($projectIds, $totalMilestones) {
            $count = ProjectMilestone::whereIn('project_id', $projectIds)
                ->where('status', $status)
                ->count();
            $percent = $totalMilestones > 0 ? (int) round(($count / $totalMilestones) * 100) : 0;

            return [
                'status'  => $status,
                'label'   => ucwords(str_replace('_', ' ', $status)),
                'count'   => $count,
                'percent' => $percent,
            ];
        });

        // ----- Milestones logged per month, last 7 months (for the chart) -----
        $months = collect(range(6, 0))->map(fn ($i) => Carbon::now()->subMonths($i));

        $chartLabels = $months->map->format('M')->values()->toArray();

        $chartData = $months->map(function (Carbon $month) use ($projectIds) {
            return ProjectMilestone::whereIn('project_id', $projectIds)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        })->values()->toArray();

        // ----- This student's assigned project(s) (replaces the testimonial slider) -----
        $myProjects = $student->projects()
            ->with('teacher')
            ->latest('projects.created_at')
            ->take(3)
            ->get();

        // ----- Today's milestones across this student's project(s) -----
        $todaysMilestones = ProjectMilestone::with('project')
            ->whereIn('project_id', $projectIds)
            ->whereDate('tentative_time', Carbon::today())
            ->orderBy('tentative_time')
            ->take(5)
            ->get();

        // ----- Recent milestone activity across this student's project(s) -----
        $recentUpdates = ProjectMilestone::with(['project', 'doneBy'])
            ->whereIn('project_id', $projectIds)
            ->latest('updated_at')
            ->take(4)
            ->get();

        return view('student.dashboard', compact(
            'totalProjects',
            'totalProjectBooks',
            'totalMilestones',
            'pendingMilestones',
            'projectBookStats',
            'milestoneStats',
            'chartLabels',
            'chartData',
            'myProjects',
            'todaysMilestones',
            'recentUpdates',
        ));
    }
}