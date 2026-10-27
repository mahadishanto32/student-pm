<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Concerns\SavesStudentMarks;
use App\Http\Controllers\Controller;
use App\Models\Presentation;
use App\Models\ProjectMark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherPresentationController extends Controller
{
    use SavesStudentMarks;

    public function index(Request $request)
    {
        $teacherId = Auth::id();

        $query = Presentation::query()
            ->with(['project', 'presenter'])
            ->whereHas('project', fn ($q) => $q->where('assigned_teacher', $teacherId));

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('presenter', fn ($p) => $p->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('project', function ($p) use ($search) {
                      $p->where('project_name', 'like', "%{$search}%")
                        ->orWhere('group_number', 'like', "%{$search}%");
                  });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $presentations = $query->latest('date_of_presentation')->paginate(15);

        // project_id => ['marked' => n, 'avg' => x] for the "Presentation" topic
        $marksSummary = ProjectMark::with(['distributions' => fn ($q) => $q->where('topic', 'Presentation')])
            ->whereIn('project_id', $presentations->pluck('project_id')->unique())
            ->get()
            ->groupBy('project_id')
            ->map(function ($rows) {
                $given = $rows
                    ->map(fn ($m) => $m->distributions->first()?->given_marks)
                    ->filter(fn ($v) => $v !== null)
                    ->map(fn ($v) => (float) $v);

                return [
                    'marked' => $given->count(),
                    'avg'    => $given->count() ? round($given->avg(), 2) : null,
                ];
            });

        return view('teachers.presentations.index', compact('presentations', 'marksSummary'));
    }

    public function show(Presentation $presentation)
    {
        $this->authorizeTeacher($presentation);

        $presentation->load(['project.teamMembers', 'presenter']);

        $students = $this->studentsOf($presentation);

        $marks = ProjectMark::with(['distributions' => fn ($q) => $q->where('topic', 'Presentation')])
            ->where('project_id', $presentation->project_id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        return view('teachers.presentations.show', compact('presentation', 'students', 'marks'));
    }

    public function edit(Presentation $presentation)
    {
        $this->authorizeTeacher($presentation);

        $presentation->load(['project.teamMembers', 'presenter']);

        $students = $this->studentsOf($presentation);

        $existingMarks = ProjectMark::with(['distributions' => fn ($q) => $q->where('topic', 'Presentation')])
            ->where('project_id', $presentation->project_id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        return view('teachers.presentations.edit', compact('presentation', 'students', 'existingMarks'));
    }

    /**
     * Update status + feedback; save individual marks when status = completed.
     */
    public function update(Request $request, Presentation $presentation)
    {
        $this->authorizeTeacher($presentation);

        $rules = [
            'supervisor_feedback' => ['nullable', 'string'],
            'status'              => ['nullable', 'in:pending,approved,rejected,completed'],
        ];

        $isCompleted = $request->input('status') === 'completed';

        if ($isCompleted) {
            $rules += [
                'marks'               => ['required', 'array'],
                'marks.*.given_marks' => ['required', 'numeric', 'min:0', 'max:' . ProjectMark::MARKS_PER_TOPIC],
                'marks.*.remarks'     => ['nullable', 'string', 'max:2000'],
            ];
        }

        $validated = $request->validate($rules, [
            'marks.*.given_marks.required' => 'Please enter marks for every student.',
            'marks.*.given_marks.max'      => 'Marks cannot exceed ' . ProjectMark::MARKS_PER_TOPIC . '.',
        ]);

        DB::transaction(function () use ($validated, $presentation, $isCompleted, $request) {
            $presentation->update([
                'supervisor_feedback' => $validated['supervisor_feedback'] ?? null,
                'status'              => $validated['status'] ?? $presentation->status,
            ]);

            if ($isCompleted) {
                $presentation->load('project.teamMembers');

                $this->saveTopicMarks(
                    $presentation->project_id,
                    $this->studentsOf($presentation)->pluck('id')->all(),
                    'Presentation',
                    $request->input('marks', [])
                );
            }
        });

        return redirect()
            ->route('teachers.presentations.show', $presentation->id)
            ->with('success', 'Presentation updated successfully.');
    }

    /** Students of the project (the logged-in teacher is never marked). */
    protected function studentsOf(Presentation $presentation)
    {
        return $presentation->project->teamMembers
            ->reject(fn ($u) => $u->id === Auth::id())
            ->values();
    }

    protected function authorizeTeacher(Presentation $presentation): void
    {
        $isSupervisor = $presentation->project
            && (int) $presentation->project->assigned_teacher === (int) Auth::id();

        abort_unless($isSupervisor, 403, 'You are not assigned to this project.');
    }
}