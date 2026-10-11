<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Support\DashboardMetrics;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $student    = Auth::user();
        $projectIds = $student->projects()->pluck('projects.id');
        $metrics    = new DashboardMetrics($projectIds);

        $projects = $metrics->projectCards(3);

        // Marks given by the supervisor (project_marks + marks_distributions)
        $marks = null;
        $row   = DB::table('project_marks')->where('student_id', $student->id)->latest('id')->first();
        if ($row) {
            $rows  = DB::table('marks_distributions')->where('project_marks_id', $row->id)->get();
            $marks = [
                'remarks' => $row->remarks,
                'rows'    => $rows,
                'given'   => (float) $rows->sum('given_marks'),
                'out_of'  => (float) $rows->sum('out_of'),
            ];
        }

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
            'marks'          => $marks,
            'notifications'  => DashboardMetrics::notifications($student),
        ]);
    }
}
