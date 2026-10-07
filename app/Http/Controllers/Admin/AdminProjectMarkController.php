<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProjectMark;
use App\Models\MarksDistribution;
use Illuminate\Http\Request;

class AdminProjectMarkController extends Controller
{
    /**
     * Display a listing of all project marks.
     */
    public function index(Request $request)
    {
        $query = ProjectMark::with(['project', 'supervisor', 'student', 'distributions']);

        // Search filter (project fields + student/supervisor name)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('project', function ($pq) use ($search) {
                    $pq->where('group_number', 'like', "%{$search}%")
                       ->orWhere('project_name', 'like', "%{$search}%")
                       ->orWhere('project_topic', 'like', "%{$search}%");
                })
                ->orWhereHas('student', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('supervisor', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Filter by project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $projectMarks = $query->latest()->paginate(15);

        // Apply grade filter (post-query since grade is a computed attribute)
        if ($request->filled('grade')) {
            $grade = $request->grade;
            $filtered = $projectMarks->getCollection()->filter(function ($mark) use ($grade) {
                return $mark->grade_info['grade'] === $grade;
            });
            $projectMarks->setCollection($filtered);
        }

        // For the project dropdown
        $projects = \App\Models\Project::orderBy('group_number')->get();

        return view('admin.project-marks.index', compact('projectMarks', 'projects'));
    }

    /**
     * Display the specified project mark with grading details.
     */
    public function show(ProjectMark $projectMark)
    {
        $projectMark->load(['project', 'supervisor', 'student', 'distributions']);

        // Grading scale for reference table
        $gradingScale = ProjectMark::GRADING_SCALE;

        // All topics
        $topics = MarksDistribution::TOPICS;

        return view('admin.project-marks.show', compact('projectMark', 'gradingScale', 'topics'));
    }
}
