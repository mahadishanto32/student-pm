<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\VideoResume;
use Illuminate\Http\Request;

class TeacherVideoResumeController extends Controller
{
    /** Projects where the logged-in teacher is the assigned teacher. */
    private function myProjects()
    {
        return Project::where('assigned_teacher', auth()->id());
    }

    public function index(Request $request)
    {
        $projects = $this->myProjects()->orderBy('project_name')->get();

        $videoResumes = VideoResume::with('project')
            ->whereIn('project_id', $projects->pluck('id'))
            ->when($request->filled('project_id'), fn ($q) => $q->where('project_id', $request->project_id))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->search;
                $q->where(function ($q) use ($s) {
                    $q->where('title', 'like', "%{$s}%")
                      ->orWhereHas('project', fn ($p) => $p->where('project_name', 'like', "%{$s}%"));
                });
            })
            ->latest()
            ->paginate(10);

        return view('teachers.video-resumes.index', compact('videoResumes', 'projects'));
    }

    public function show($id)
    {
        $videoResume = VideoResume::with('project.teamMembers')
            ->whereIn('project_id', $this->myProjects()->pluck('id'))
            ->findOrFail($id);

        return view('teachers.video-resumes.show', compact('videoResume'));
    }
}
