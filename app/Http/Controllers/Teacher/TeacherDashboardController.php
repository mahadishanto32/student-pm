<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Support\DashboardMetrics;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Concerns\BuildsProjectMarkSheets;
use App\Models\ProjectMark;

class TeacherDashboardController extends Controller
{
    use BuildsProjectMarkSheets;

    public function index()
    {
        $teacher    = Auth::user();
        $projectIds = Project::where('assigned_teacher', $teacher->id)->pluck('id');
        $metrics    = new DashboardMetrics($projectIds);

        $stats = $metrics->counters();

        // Students under supervision and whether marks were entered for them
        $students = DB::table('project_user')
            ->join('users', 'users.id', '=', 'project_user.user_id')
            ->join('projects', 'projects.id', '=', 'project_user.project_id')
            ->whereIn('project_user.project_id', $projectIds)
            ->select('users.id', 'users.name', 'projects.project_name')
            ->get();

        $markedIds = DB::table('project_marks')
            ->where('supervisor_id', $teacher->id)->pluck('student_id')->all();

        $marking = $students->map(fn ($s) => [
            'name'    => $s->name,
            'project' => $s->project_name,
            'marked'  => in_array($s->id, $markedIds),
        ]);

        $stats['students'] = $students->pluck('id')->unique()->count();
        $stats['marked']   = $marking->where('marked', true)->count();

        // Full mark sheet per assigned project
        $markProjects = Project::with('teamMembers')->whereIn('id', $projectIds)->orderBy('group_number')->get();
        $markSheets   = $this->buildSheets($markProjects);

        return view('teachers.dashboard', [
            'stats'          => $stats,
            'marking'        => $marking,
            'bookStats'      => $metrics->bookStats(),
            'milestoneStats' => $metrics->milestoneStats(),
            'chart'          => $metrics->monthlyActivity(),
            'projects'       => $metrics->projectCards(5),
            'attention'      => $metrics->attention(5),
            'updates'        => $metrics->recentUpdates(5),
            'schedule'       => $metrics->schedule(5),
            'notifications'  => DashboardMetrics::notifications($teacher),
            'markProjects'   => $markProjects,
            'markSheets'     => $markSheets,
            'topicMap'       => $this->topicMap(),
            'perTopic'       => ProjectMark::MARKS_PER_TOPIC,
        ]);
    }
}
