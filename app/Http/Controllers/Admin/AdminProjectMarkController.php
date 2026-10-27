<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\BuildsProjectMarkSheets;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectMark;
use Illuminate\Http\Request;

class AdminProjectMarkController extends Controller
{
    use BuildsProjectMarkSheets;

    public function index(Request $request)
    {
        $projects = Project::with(['teamMembers', 'teacher'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->search;

                $q->where(function ($q) use ($s) {
                    $q->where('project_name', 'like', "%{$s}%")
                      ->orWhere('project_topic', 'like', "%{$s}%")
                      ->orWhere('group_number', 'like', "%{$s}%")
                      // NEW: match projects whose team members have this class_id
                      ->orWhereHas('teamMembers', function ($q) use ($s) {
                          $q->where('class_id', 'like', "%{$s}%");
                      });
                });
            })
            ->orderBy('group_number')
            ->paginate(10)
            ->withQueryString();

        // Build summaries and attach student names for the view
        $summaries = $this->summaries($projects->getCollection());

        foreach ($projects as $project) {
            $names = $project->teamMembers
                ->pluck('name')
                ->filter()
                ->values()
                ->all();

            $summaries[$project->id]['student_names'] = $names;
        }

        return view('admin.project-marks.index', compact('projects', 'summaries'));
    }

    public function show(string $id)
    {
        $project  = Project::with(['teamMembers', 'teacher'])->findOrFail($id);
        $rows     = $this->buildSheets(collect([$project]))[$project->id];
        $topicMap = $this->topicMap();
        $perTopic = ProjectMark::MARKS_PER_TOPIC;

        return view('admin.project-marks.show', compact('project', 'rows', 'topicMap', 'perTopic'));
    }
}