<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\MarksDistribution;
use App\Models\Project;
use App\Models\ProjectMark;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherProjectMarkController extends Controller
{
    public function index(Request $request)
    {
        $teacherId = Auth::id();

        $marks = ProjectMark::with(['project', 'student', 'distributions'])
            ->where('supervisor_id', $teacherId)
            ->when($request->filled('project_id'), fn ($q) => $q->where('project_id', $request->project_id))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->search;
                $q->where(function ($q) use ($s) {
                    $q->whereHas('student', fn ($u) => $u->where('name', 'like', "%{$s}%"))
                      ->orWhereHas('project', fn ($p) => $p->where('group_number', 'like', "%{$s}%")
                                                           ->orWhere('project_name', 'like', "%{$s}%"));
                });
            })
            ->latest()
            ->paginate(10);

        $projects = Project::where('assigned_teacher', $teacherId)
            ->orderBy('group_number')
            ->get();

        return view('teachers.project-marks.index', compact('marks', 'projects'));
    }

    public function create()
    {
        $teacherId = Auth::id();

        $projects = Project::with('teamMembers:users.id,users.name')
            ->where('assigned_teacher', $teacherId)
            ->orderBy('group_number')
            ->get();

        // students who already have a mark record, grouped by project
        $marked = ProjectMark::whereIn('project_id', $projects->pluck('id'))
            ->get(['project_id', 'student_id'])
            ->groupBy('project_id')
            ->map(fn ($g) => $g->pluck('student_id')->all());

        $projectData = $projects->map(fn ($p) => [
            'id'       => $p->id,
            'label'    => 'Group ' . $p->group_number . ' — ' . ($p->project_name ?? 'Untitled'),
            'students' => $p->teamMembers->map(fn ($u) => [
                'id'     => $u->id,
                'name'   => $u->name,
                'marked' => in_array($u->id, $marked[$p->id] ?? []),
            ])->values(),
        ])->values();

        return view('teachers.project-marks.create', [
            'projectData' => $projectData,
            'topics'      => MarksDistribution::TOPICS,
            'outOf'       => ProjectMark::MARKS_PER_TOPIC,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(
            array_merge([
                'project_id' => ['required', 'integer', 'exists:projects,id'],
                'student_id' => ['required', 'integer', 'exists:users,id'],
            ], $this->marksRules()),
            [],
            $this->attributeNames()
        );

        $teacherId = Auth::id();

        // Teacher may mark only his assigned projects
        $project = Project::where('id', $data['project_id'])
            ->where('assigned_teacher', $teacherId)
            ->firstOrFail();

        // Student must belong to this project's team
        abort_unless(
            $project->teamMembers()->where('users.id', $data['student_id'])->exists(),
            403,
            'This student is not a member of the selected project.'
        );

        if (ProjectMark::where('project_id', $project->id)->where('student_id', $data['student_id'])->exists()) {
            return back()->withInput()->with('error', 'Marks have already been given to this student for this project. Please edit the existing record.');
        }

        DB::transaction(function () use ($data, $project, $teacherId) {
            $mark = ProjectMark::create([
                'project_id'    => $project->id,
                'supervisor_id' => $teacherId,
                'student_id'    => $data['student_id'],
                'remarks'       => $data['remarks'] ?? null,
            ]);

            $this->saveDistributions($mark, array_values($data['marks']));
        });

        return redirect()->route('teachers.project-marks.index')
            ->with('success', 'Marks saved successfully.');
    }

    public function show($id)
    {
        $mark = $this->findOwned($id);

        return view('teachers.project-marks.show', [
            'mark'          => $mark,
            'distributions' => $mark->distributions->keyBy('topic'),
            'topics'        => MarksDistribution::TOPICS,
            'gradingScale'  => ProjectMark::GRADING_SCALE,
        ]);
    }

    public function edit($id)
    {
        $mark = $this->findOwned($id);

        return view('teachers.project-marks.edit', [
            'mark'          => $mark,
            'distributions' => $mark->distributions->keyBy('topic'),
            'topics'        => MarksDistribution::TOPICS,
            'outOf'         => ProjectMark::MARKS_PER_TOPIC,
        ]);
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $mark = $this->findOwned($id);

        $data = $request->validate($this->marksRules(), [], $this->attributeNames());

        DB::transaction(function () use ($mark, $data) {
            $mark->update(['remarks' => $data['remarks'] ?? null]);
            $this->saveDistributions($mark, array_values($data['marks']));
        });

        return redirect()->route('teachers.project-marks.show', $mark->id)
            ->with('success', 'Marks updated successfully.');
    }

    public function destroy($id): RedirectResponse
    {
        $mark = $this->findOwned($id);
        $mark->delete(); // distributions removed by cascade

        return redirect()->route('teachers.project-marks.index')
            ->with('success', 'Marks deleted successfully.');
    }

    /* ---------------- helpers ---------------- */

    private function findOwned($id): ProjectMark
    {
        return ProjectMark::with(['project', 'student', 'supervisor', 'distributions'])
            ->where('supervisor_id', Auth::id())
            ->findOrFail($id);
    }

    private function marksRules(): array
    {
        return [
            'remarks'   => ['nullable', 'string', 'max:2000'],
            'marks'     => ['required', 'array', 'size:' . count(MarksDistribution::TOPICS)],
            'marks.*'   => ['required', 'numeric', 'min:0', 'max:' . ProjectMark::MARKS_PER_TOPIC],
        ];
    }

    private function attributeNames(): array
    {
        $names = [];
        foreach (MarksDistribution::TOPICS as $i => $topic) {
            $names["marks.$i"] = strtolower($topic) . ' marks';
        }
        return $names;
    }

    /**
     * $marks is a 0-indexed list in the same order as MarksDistribution::TOPICS.
     * out_of is always forced to 20 on the server.
     */
    private function saveDistributions(ProjectMark $mark, array $marks): void
    {
        foreach (MarksDistribution::TOPICS as $i => $topic) {
            $values = [
                'given_marks' => $marks[$i],
                'out_of'      => ProjectMark::MARKS_PER_TOPIC,
            ];

            $query = $mark->distributions()->where('topic', $topic);

            if ($query->exists()) {
                $query->update($values);
            } else {
                $mark->distributions()->create(['topic' => $topic] + $values);
            }
        }
    }
}
