<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\BuildsProjectMarkSheets;
use App\Http\Controllers\Controller;
use App\Models\MarksDistribution;
use App\Models\Project;
use App\Models\ProjectMark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminProjectMarkController extends Controller
{
    use BuildsProjectMarkSheets;

    /** Topics the admin is allowed to edit */
    protected const ADMIN_EDITABLE_TOPICS = ['Project Book', 'Presentation'];

    public function index(Request $request)
    {
        $projects = Project::with(['teamMembers', 'teacher'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->search;

                $q->where(function ($q) use ($s) {
                    $q->where('project_name', 'like', "%{$s}%")
                      ->orWhere('project_topic', 'like', "%{$s}%")
                      ->orWhere('group_number', 'like', "%{$s}%")
                      ->orWhereHas('teamMembers', fn ($q) =>
                          $q->where('class_id', 'like', "%{$s}%"));
                });
            })
            ->orderBy('group_number')
            ->paginate(10)
            ->withQueryString();

        $summaries = $this->summaries($projects->getCollection());

        foreach ($projects as $project) {
            $summaries[$project->id]['student_names'] = $project->teamMembers
                ->pluck('name')->filter()->values()->all();
        }

        return view('admin.project-marks.index', compact('projects', 'summaries'));
    }

    public function show(string $id)
    {
        $project  = Project::with(['teamMembers', 'teacher'])->findOrFail($id);
        $rows     = $this->buildSheets(collect([$project]))[$project->id];
        $topicMap = $this->topicMap();

        return view('admin.project-marks.show', compact('project', 'rows', 'topicMap'));
    }

    public function edit(string $id)
    {
        $project  = Project::with(['teamMembers', 'teacher'])->findOrFail($id);
        $rows     = $this->buildSheets(collect([$project]))[$project->id];
        $topicMap = $this->topicMap();

        // What the admin may edit (subset of all topics)
        $editableTopics = collect($topicMap)
            ->filter(fn ($topic) => in_array($topic, self::ADMIN_EDITABLE_TOPICS, true))
            ->toArray();

        return view('admin.project-marks.edit',
            compact('project', 'rows', 'topicMap', 'editableTopics'));
    }

    public function update(Request $request, string $id)
    {
        $project  = Project::with('teamMembers')->findOrFail($id);
        $students = $project->teamMembers->where('role', 'student');
        $topicMax = MarksDistribution::TOPIC_MAX;

        $rules = [
            'marks'           => ['required', 'array'],
            'marks.*.remarks' => ['nullable', 'string', 'max:2000'],
        ];
        $messages = [];

        foreach (self::ADMIN_EDITABLE_TOPICS as $topic) {
            // form key is snake_case version: project_book / presentation
            $key = strtolower(str_replace(' ', '_', $topic));
            $max = $topicMax[$topic];

            $rules["marks.*.{$key}"]            = ['nullable', 'numeric', 'min:0', "max:{$max}"];
            $messages["marks.*.{$key}.max"]     = "{$topic} marks cannot exceed {$max}.";
            $messages["marks.*.{$key}.min"]     = "{$topic} marks cannot be negative.";
            $messages["marks.*.{$key}.numeric"] = "{$topic} marks must be a number.";
        }

        $request->validate($rules, $messages);

        try {
            DB::transaction(function () use ($request, $project, $students, $topicMax) {
                $now = now();

                foreach ($students as $student) {
                    $input = $request->input("marks.{$student->id}");
                    if (! is_array($input)) {
                        continue;
                    }

                    $remarks = trim((string) ($input['remarks'] ?? ''));

                    // Collect only the admin-editable values
                    $values = [];
                    foreach (self::ADMIN_EDITABLE_TOPICS as $topic) {
                        $key        = strtolower(str_replace(' ', '_', $topic));
                        $v          = $input[$key] ?? null;
                        $values[$topic] = ($v === null || $v === '') ? null : $v;
                    }

                    $hasAny = collect($values)->contains(fn ($v) => $v !== null) || $remarks !== '';

                    $pm = ProjectMark::where('project_id', $project->id)
                        ->where('student_id', $student->id)
                        ->first();

                    if (! $pm && ! $hasAny) {
                        continue;
                    }

                    $pm ??= new ProjectMark([
                        'project_id' => $project->id,
                        'student_id' => $student->id,
                    ]);
                    // Don't overwrite supervisor_id if already set by the teacher
                    if (! $pm->supervisor_id) {
                        $pm->supervisor_id = Auth::id();
                    }
                    $pm->remarks = $remarks !== '' ? $remarks : null;
                    $pm->save();

                    // Write only editable topics — Milestones is never touched here
                    foreach ($values as $topic => $given) {
                        $query = DB::table('marks_distributions')
                            ->where('project_marks_id', $pm->id)
                            ->where('topic', $topic);

                        if ($given === null) {
                            $query->delete();
                            continue;
                        }

                        $data = [
                            'given_marks' => $given,
                            'out_of'      => $topicMax[$topic],
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
            ->route('admin.project-marks.show', $project->id)
            ->with('success', 'Project Book and Presentation marks updated.');
    }
}