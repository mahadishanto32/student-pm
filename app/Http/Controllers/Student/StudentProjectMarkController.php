<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ProjectMark;
use App\Models\MarksDistribution;
use Illuminate\Http\Request;

class StudentProjectMarkController extends Controller
{
    /**
     * Display a listing of the student's project marks.
     */
    public function index(Request $request)
    {
        $student = auth()->user();

        // Get all projects the student is part of
        $projectIds = $student->projects()->pluck('projects.id');

        // Get marks for the student in those projects
        $query = ProjectMark::with(['project', 'supervisor', 'distributions'])
            ->where('student_id', $student->id)
            ->whereIn('project_id', $projectIds);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('project', function ($q) use ($search) {
                $q->where('group_number', 'like', "%{$search}%")
                  ->orWhere('project_name', 'like', "%{$search}%")
                  ->orWhere('project_topic', 'like', "%{$search}%");
            });
        }

        $projectMarks = $query->latest()->paginate(10);

        // Apply grade filter (post-query since grade is a computed attribute)
        if ($request->filled('grade')) {
            $grade = $request->grade;
            $filtered = $projectMarks->getCollection()->filter(function ($mark) use ($grade) {
                return $mark->grade_info['grade'] === $grade;
            });
            $projectMarks->setCollection($filtered);
        }

        return view('student.project-marks.index', compact('projectMarks'));
    }

    /**
     * Display the specified project mark with grading details.
     */
    public function show(ProjectMark $projectMark)
    {
        $student = auth()->user();

        // Ensure the student owns this mark
        if ($projectMark->student_id !== $student->id) {
            abort(403, 'You are not authorized to view this record.');
        }

        // Verify student is part of the project
        $isMember = $student->projects()
            ->where('projects.id', $projectMark->project_id)
            ->exists();

        if (! $isMember) {
            abort(403, 'You are not a member of this project.');
        }

        $projectMark->load(['project', 'supervisor', 'distributions']);

        // Grading scale for reference table
        $gradingScale = ProjectMark::GRADING_SCALE;

        // All topics
        $topics = MarksDistribution::TOPICS;

        return view('student.project-marks.show', compact('projectMark', 'gradingScale', 'topics'));
    }
}
