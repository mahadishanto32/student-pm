<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Concerns\BuildsProjectMarkSheets;
use App\Http\Controllers\Controller;
use App\Models\MarksDistribution;
use Illuminate\Support\Facades\Auth;

class StudentProjectMarkController extends Controller
{
    use BuildsProjectMarkSheets;

    /** Show all topics only when the project is completed */
    protected function visibleTopicsFor($project): array
    {
        $all = array_values($this->topicMap());

        if (($project->status ?? null) === 'completed') {
            return $all;
        }

        // Only Milestones until the project is completed
        return array_values(array_filter(
            $all,
            fn ($t) => $t === 'Milestones'
        ));
    }

    public function index()
    {
        $projects = Auth::user()
            ->projects()
            ->with('teamMembers')
            ->orderBy('projects.group_number')
            ->get();

        $sheets   = $this->buildSheets($projects, Auth::id());
        $topicMap = $this->topicMap();

        // project_id => array of visible topics
        $visibleTopics = [];
        foreach ($projects as $project) {
            $visibleTopics[$project->id] = $this->visibleTopicsFor($project);
        }

        return view('student.project-marks.index',
            compact('projects', 'sheets', 'topicMap', 'visibleTopics'));
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
        $visibleTopics = $this->visibleTopicsFor($project);

        return view('student.project-marks.show',
            compact('project', 'rows', 'topicMap', 'visibleTopics'));
    }
}