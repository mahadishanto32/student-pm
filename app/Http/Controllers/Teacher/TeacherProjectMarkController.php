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
        $max      = ProjectMark::MARKS_PER_TOPIC;

        $rules    = [
            'marks'           => ['required', 'array'],
            'marks.*.remarks' => ['nullable', 'string', 'max:2000'],
        ];
        $messages = [];

        foreach ($this->topicMap() as $key => $topic) {
            $rules["marks.*.{$key}"]          = ['nullable', 'numeric', 'min:0', "max:{$max}"];
            $messages["marks.*.{$key}.max"]   = "{$topic} marks cannot exceed {$max}.";
            $messages["marks.*.{$key}.min"]   = "{$topic} marks cannot be negative.";
            $messages["marks.*.{$key}.numeric"] = "{$topic} marks must be a number.";
        }

        $request->validate($rules, $messages);

        try {
            DB::transaction(function () use ($request, $project, $students, $max) {
                $now = now();

                foreach ($students as $student) {
                    $input = $request->input("marks.{$student->id}");
                    if (! is_array($input)) {
                        continue;
                    }

                    $remarks = trim((string) ($input['remarks'] ?? ''));
                    $values  = [];

                    foreach ($this->topicMap() as $key => $topic) {
                        $v = $input[$key] ?? null;
                        $values[$topic] = ($v === null || $v === '') ? null : $v;
                    }

                    $hasAny = collect($values)->contains(fn ($v) => $v !== null) || $remarks !== '';

                    $pm = ProjectMark::where('project_id', $project->id)
                        ->where('student_id', $student->id)
                        ->first();

                    if (! $pm && ! $hasAny) {
                        continue; // nothing to store
                    }

                    $pm ??= new ProjectMark([
                        'project_id' => $project->id,
                        'student_id' => $student->id,
                    ]);
                    $pm->supervisor_id = Auth::id();
                    $pm->remarks       = $remarks !== '' ? $remarks : null;
                    $pm->save();

                    foreach ($values as $topic => $given) {
                        $query = DB::table('marks_distributions')
                            ->where('project_marks_id', $pm->id)
                            ->where('topic', $topic);

                        if ($given === null) {
                            $query->delete(); // blank = clear this topic's mark
                            continue;
                        }

                        $data = [
                            'given_marks' => $given,
                            'out_of'      => $max,
                            'updated_at'  => $now,
                        ];

                        if ($query->exists()) {
                            $query->update($data);
                        } else {
                            DB::table('marks_distributions')->insert($data + [
                                'project_marks_id' => $pm->id,
                                'topic'            => $topic,
                                'created_at'       => $now,
                            ]);
                        }
                    }
                }
            });
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', 'Failed to save marks: ' . $e->getMessage());
        }

        return redirect()
            ->route('teachers.project-marks.show', $project->id)
            ->with('success', 'Project marks updated successfully.');
    }
}