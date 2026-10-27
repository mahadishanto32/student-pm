<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Concerns\BuildsProjectMarkSheets;
use App\Http\Controllers\Controller;
use App\Models\ProjectMark;
use Illuminate\Support\Facades\Auth;

class StudentProjectMarkController extends Controller
{
    use BuildsProjectMarkSheets;

    public function index()
    {
        $projects = Auth::user()
            ->projects()
            ->with('teamMembers')
            ->orderBy('projects.group_number')
            ->get();

        // project_id => one row (this student only)
        $sheets   = $this->buildSheets($projects, Auth::id());
        $topicMap = $this->topicMap();
        $perTopic = ProjectMark::MARKS_PER_TOPIC;

        return view('student.project-marks.index', compact('projects', 'sheets', 'topicMap', 'perTopic'));
    }

    public function show(string $id)
    {
        $project = Auth::user()
            ->projects()
            ->with('teamMembers')
            ->where('projects.id', $id)
            ->firstOrFail();

        $rows     = $this->buildSheets(collect([$project]), Auth::id())[$project->id];
        $topicMap = $this->topicMap();
        $perTopic = ProjectMark::MARKS_PER_TOPIC;

        return view('student.project-marks.show', compact('project', 'rows', 'topicMap', 'perTopic'));
    }
}