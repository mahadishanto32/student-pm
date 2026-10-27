<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Support\DashboardMetrics;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Concerns\BuildsProjectMarkSheets;
use App\Models\ProjectMark;

class StudentDashboardController extends Controller
{
    use BuildsProjectMarkSheets;

    public function index()
    {
        $student    = Auth::user();
        $projectIds = $student->projects()->pluck('projects.id');
        $metrics    = new DashboardMetrics($projectIds);

        $projects = $metrics->projectCards(3);

        // Own marks per project (project_marks + marks_distributions)
        $markProjects = $student->projects()->with('teamMembers')->orderBy('projects.group_number')->get();
        $markSheets   = $this->buildSheets($markProjects, $student->id);

        $markCards = $markProjects->map(function ($p) use ($markSheets) {
            $row = ($markSheets[$p->id] ?? collect())->first();
            $any = $row && collect($row['marks'])->contains(fn ($v) => $v !== null);

            return ['name' => $p->project_name, 'group' => $p->group_number, 'row' => $any ? $row : null];
        });

        return view('student.dashboard', [
            'stats'          => $metrics->counters(),
            'bookStats'      => $metrics->bookStats(),
            'milestoneStats' => $metrics->milestoneStats(),
            'chart'          => $metrics->monthlyActivity(),
            'projects'       => $projects,
            'nextEnd'        => $projects->whereNotNull('daysLeft')->sortBy('daysLeft')->first(),
            'attention'      => $metrics->attention(5),
            'updates'        => $metrics->recentUpdates(5),
            'schedule'       => $metrics->schedule(5),
            'markCards' => $markCards,
            'topicMap'  => $this->topicMap(),
            'perTopic'  => ProjectMark::MARKS_PER_TOPIC,
            'notifications'  => DashboardMetrics::notifications($student),
        ]);
    }
}
