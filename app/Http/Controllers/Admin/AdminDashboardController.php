<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use App\Http\Controllers\Concerns\BuildsProjectMarkSheets;
use App\Models\ProjectMark;
use App\Support\DashboardMetrics;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class AdminDashboardController extends Controller
{
    use BuildsProjectMarkSheets;

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

        // Marks overview across all projects
        $markProjects = Project::with(['teamMembers', 'teacher'])->get();
        $markSheets   = $this->buildSheets($markProjects);

        $allRows = collect($markSheets)->flatten(1);

        $gradeDist = collect(ProjectMark::GRADING_SCALE)->pluck('grade')
            ->mapWithKeys(fn ($g) => [$g => $allRows->where('grade', $g)->count()])
            ->put('I', $allRows->where('grade', 'I')->count());

        $markCards = $markProjects->map(function ($p) use ($markSheets) {
            $rows     = $markSheets[$p->id] ?? collect();
            $complete = $rows->where('complete', true);
            $avg      = $complete->count() ? round($complete->avg('percentage'), 1) : null;
            $total    = $complete->count() ? round($complete->avg('total'), 1) : null;

            return [
                'id'       => $p->id,
                'name'     => $p->project_name,
                'group'    => $p->group_number,
                'teacher'  => $p->teacher->name ?? null,
                'students' => $rows->count(),
                'complete' => $complete->count(),
                'avg'      => $avg,
                'total'    => $total,
                'grade'    => $avg !== null ? $this->gradeFor($avg)['grade'] : 'I',
            ];
        })
        ->sortByDesc(fn ($c) => $c['avg'] ?? -1)
        ->take(8)
        ->values();

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
            'markCards'      => $markCards,
            'gradeDist'      => $gradeDist,
            'markStudents'   => $allRows->count(),
            'markGraded'     => $allRows->where('complete', true)->count(),
            'perTopic'       => ProjectMark::MARKS_PER_TOPIC,
            'markOutOf'      => ProjectMark::MARKS_PER_TOPIC * count($this->topicMap()),
        ]);
    }
}
