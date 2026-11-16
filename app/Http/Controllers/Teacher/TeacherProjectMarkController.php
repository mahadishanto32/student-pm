<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Concerns\BuildsProjectMarkSheets;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectMark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherProjectMarkController extends Controller
{
    use BuildsProjectMarkSheets;

    /** Only projects assigned to the logged-in teacher. */
    protected function findProject(string $id): Project
    {
        return Project::with('teamMembers')
            ->where('assigned_teacher', Auth::id())
            ->findOrFail($id);
    }

    public function index(Request $request)
    {
        $projects = Project::with('teamMembers')
            ->where('assigned_teacher', Auth::id())
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->search;

                $q->where(function ($q) use ($s) {
                    $q->where('project_name', 'like', "%{$s}%")
                    ->orWhere('project_topic', 'like', "%{$s}%")
                    ->orWhere('group_number', 'like', "%{$s}%")
                    // NEW: partial match against team members' class_id
                    ->orWhereHas('teamMembers', function ($sub) use ($s) {
                        $sub->where('class_id', 'like', "%{$s}%");
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

        return view('teachers.project-marks.index', compact('projects', 'summaries'));
    }

    public function show(string $id)
    {
        $project  = $this->findProject($id);
        $rows     = $this->buildSheets(collect([$project]))[$project->id];
        $topicMap = $this->topicMap();
        $perTopic = ProjectMark::MARKS_PER_TOPIC;

        return view('teachers.project-marks.show', compact('project', 'rows', 'topicMap', 'perTopic'));
    }

    public function edit(string $id)
    {
        $project  = $this->findProject($id);
        $rows     = $this->buildSheets(collect([$project]))[$project->id];
        $topicMap = $this->topicMap();
        $perTopic = ProjectMark::MARKS_PER_TOPIC;

        return view('teachers.project-marks.edit', compact('project', 'rows', 'topicMap', 'perTopic'));
    }

    public function update(Request $request, string $id)
    {
        $project  = $this->findProject($id);
        $students = $project->teamMembers->where('role', 'student');

        $request->validate([
            'marks'           => ['required', 'array'],
            'marks.*.remarks' => ['nullable', 'string', 'max:2000'],
            // NOTE: no marks.*.milestones rule — it's auto-computed
        ], [
            'marks.required' => 'No marks payload received.',
        ]);

        DB::transaction(function () use ($request, $project, $students) {
            foreach ($students as $student) {
                $input   = $request->input("marks.{$student->id}");
                if (! is_array($input)) continue;

                $remarks = trim((string) ($input['remarks'] ?? ''));

                $pm = ProjectMark::firstOrCreate(
                    ['project_id' => $project->id, 'student_id' => $student->id],
                    ['supervisor_id' => Auth::id()]
                );
                $pm->supervisor_id = Auth::id();
                $pm->remarks       = $remarks !== '' ? $remarks : null;
                $pm->save();

                // Re-derive Milestones from source of truth
                ProjectMark::syncMilestoneScore($project->id, $student->id);
            }
        });

        return redirect()
            ->route('teachers.project-marks.show', $project->id)
            ->with('success', 'Remarks updated. Milestones score refreshed from milestone marks.');
    }

    // Editable topics only — teacher can only change Milestones,
    // and even that value should really come from milestones, not be typed.
    protected function editableTopicMap(): array
    {
        return ['milestones' => 'Milestones'];
    }
}