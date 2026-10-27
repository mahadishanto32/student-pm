<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\ProjectMark;
use App\Models\MarksDistribution;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use App\Notifications\MilestoneCreatedNotification;
use Illuminate\Support\Facades\Notification;

class TeacherProjectMilestoneController extends Controller
{
    protected array $allowedMimes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx'];

    /**
     * IDs of projects assigned to the logged-in teacher.
     */
    protected function assignedProjectIds(): array
    {
        return Project::where('assigned_teacher', Auth::id())
            ->pluck('id')
            ->toArray();
    }

    protected function statuses(): array
    {
        return [
            ProjectMilestone::STATUS_PENDING,
            ProjectMilestone::STATUS_NEED_CORRECTION,
            ProjectMilestone::STATUS_COMPLETED,
            ProjectMilestone::STATUS_REJECTED,
        ];
    }

    /**
     * Index — milestones from projects assigned to this teacher.
     */
    public function index(Request $request)
    {
        $projectIds = $this->assignedProjectIds();

        $milestones = ProjectMilestone::with(['project.teamMembers', 'doneBy', 'tasks'])
            ->whereIn('project_id', $projectIds)
            ->when($request->filled('project_id'), fn ($q) =>
                $q->where('project_id', $request->project_id))
            ->when($request->filled('status'), fn ($q) =>
                $q->where('status', $request->status))
            ->when($request->filled('search'), fn ($q) =>
                $q->where('title', 'like', '%' . $request->search . '%'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $projects = Project::whereIn('id', $projectIds)
            ->orderBy('group_number')
            ->get();

        // project_id => ['marked' => n, 'avg' => x] for the "Milestones" topic
        $marksSummary = ProjectMark::with(['distributions' => fn ($q) => $q->where('topic', 'Milestones')])
            ->whereIn('project_id', $milestones->pluck('project_id')->unique())
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

        return view('teachers.milestones.index', compact('milestones', 'projects', 'marksSummary'));
    }

    /**
     * Create form.
     */
    public function create(Request $request)
    {
        $projects = Project::whereIn('id', $this->assignedProjectIds())
            ->orderBy('group_number')
            ->get();

        if ($projects->isEmpty()) {
            return redirect()
                ->route('teachers.milestones.index')
                ->with('error', 'You are not assigned to any project.');
        }

        $selectedProjectId = $request->get('project_id');

        return view('teachers.milestones.create', compact('projects', 'selectedProjectId'));
    }

    /**
     * Store a milestone + its tasks.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id'              => ['required', Rule::in($this->assignedProjectIds())],
            'title'                   => ['required', 'string', 'max:255'],
            'tentative_time'          => ['nullable', 'date'],
            'status'                  => ['nullable', Rule::in($this->statuses())],
            'supervisor_note'         => ['nullable', 'string', 'max:5000'],

            'tasks'                   => ['required', 'array', 'min:1'],
            'tasks.*.key_points'      => ['required', 'string', 'max:1000'],
            'tasks.*.description'     => ['nullable', 'string', 'max:5000'],
            'tasks.*.document'        => ['nullable', 'file', 'mimes:' . implode(',', $this->allowedMimes), 'max:5120'],
        ]);

        DB::beginTransaction();

        try {
            $milestone = ProjectMilestone::create([
                'project_id'      => $validated['project_id'],
                'title'           => $validated['title'],
                'tentative_time'  => $validated['tentative_time'] ?? null,
                'status'          => $validated['status'] ?? ProjectMilestone::STATUS_PENDING,
                'supervisor_note' => $validated['supervisor_note'] ?? null,
                'done_by'         => Auth::id(),
            ]);

            $this->syncTasks($milestone, $request);

            DB::commit();

            $this->notifyStudents($milestone);

            return redirect()
                ->route('teachers.milestones.show', $milestone->id)
                ->with('success', 'Milestone created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create milestone: ' . $e->getMessage());
        }
    }

    /**
     * Show a milestone belonging to one of the teacher's projects.
     */
    public function show(string $id)
    {
        $milestone = ProjectMilestone::with(['project.teamMembers', 'doneBy', 'tasks'])
            ->whereIn('project_id', $this->assignedProjectIds())
            ->findOrFail($id);

        $students = $milestone->project->teamMembers;

        $marks = ProjectMark::with('distributions')
            ->where('project_id', $milestone->project_id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        return view('teachers.milestones.show', compact('milestone', 'students', 'marks'));
    }
    /**
     * Edit form.
     */
    public function edit(string $id)
    {
        $milestone = ProjectMilestone::with(['tasks', 'project.teamMembers'])
            ->whereIn('project_id', $this->assignedProjectIds())
            ->findOrFail($id);

        $projects = Project::whereIn('id', $this->assignedProjectIds())
            ->orderBy('group_number')
            ->get();

        $students = $milestone->project->teamMembers;

        $existingMarks = ProjectMark::with('distributions')
            ->where('project_id', $milestone->project_id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        return view('teachers.milestones.edit', compact('milestone', 'projects', 'students', 'existingMarks'));
    }

    /**
     * Review a milestone: status, marks and supervisor note only.
     */
    public function update(Request $request, string $id)
    {
        $milestone = ProjectMilestone::with('tasks')
            ->whereIn('project_id', $this->assignedProjectIds())
            ->findOrFail($id);

        $rules = [
            'project_id'              => ['required', Rule::in($this->assignedProjectIds())],
            'title'                   => ['required', 'string', 'max:255'],
            'tentative_time'          => ['nullable', 'date'],
            'status'                  => ['required', Rule::in($this->statuses())],
            'supervisor_note'         => ['nullable', 'string', 'max:5000'],
            'tasks'                   => ['required', 'array', 'min:1'],
            'tasks.*.id'              => ['nullable', 'integer'],
            'tasks.*.key_points'      => ['required', 'string', 'max:1000'],
            'tasks.*.description'     => ['nullable', 'string', 'max:5000'],
            'tasks.*.document'        => ['nullable', 'file', 'mimes:' . implode(',', $this->allowedMimes), 'max:5120'],
            'tasks.*.remove_document' => ['nullable', 'boolean'],
        ];

        $isCompleted = $request->input('status') === ProjectMilestone::STATUS_COMPLETED;

        if ($isCompleted) {
            $rules += [
                'marks'                     => ['required', 'array'],
                'marks.*.given_marks'       => ['required', 'numeric', 'min:0', 'max:' . ProjectMark::MARKS_PER_TOPIC],
                'marks.*.remarks'           => ['nullable', 'string', 'max:2000'],
            ];
        }

        $validated = $request->validate($rules, [
            'marks.*.given_marks.required' => 'Please enter marks for every student.',
            'marks.*.given_marks.max'      => 'Marks cannot exceed ' . ProjectMark::MARKS_PER_TOPIC . '.',
        ]);

        DB::beginTransaction();

        try {
            $milestone->update([
                'project_id'      => $validated['project_id'],
                'title'           => $validated['title'],
                'tentative_time'  => $validated['tentative_time'] ?? null,
                'status'          => $validated['status'],
                'supervisor_note' => $validated['supervisor_note'] ?? null,
            ]);

            $this->syncTasks($milestone, $request);

            if ($isCompleted) {
                $milestone->load('project.teamMembers');
                $this->saveStudentMarks($milestone, $request->input('marks', []));
            }

            DB::commit();

            return redirect()
                ->route('teachers.milestones.show', $milestone->id)
                ->with('success', 'Milestone updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update milestone: ' . $e->getMessage());
        }
    }

    /**
     * Delete a milestone and its tasks/documents.
     */
    public function destroy(string $id)
    {
        $milestone = ProjectMilestone::with('tasks')
            ->whereIn('project_id', $this->assignedProjectIds())
            ->findOrFail($id);

        DB::beginTransaction();

        try {
            foreach ($milestone->tasks as $task) {
                $this->deleteDocument($task->document);
            }

            $milestone->tasks()->delete();
            $milestone->delete();

            DB::commit();

            return redirect()
                ->route('teachers.milestones.index')
                ->with('success', 'Milestone deleted successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete milestone: ' . $e->getMessage());
        }
    }

    /* ------------------------------------------------------------------ */
    /* Helpers                                                             */
    /* ------------------------------------------------------------------ */

    /**
     * Sync tasks: update existing, create new, delete removed.
     * Works for both store (no ids) and update.
     */
    protected function syncTasks(ProjectMilestone $milestone, Request $request): void
    {
        $rows        = $request->input('tasks', []);
        $files       = $request->file('tasks', []);
        $keptTaskIds = [];

        foreach ($rows as $index => $row) {
            $task = null;

            if (!empty($row['id'])) {
                $task = $milestone->tasks()->where('id', $row['id'])->first();
            }

            $documentPath = $task->document ?? null;

            if (!empty($row['remove_document'])) {
                $this->deleteDocument($documentPath);
                $documentPath = null;
            }

            if (isset($files[$index]['document']) && $files[$index]['document'] instanceof UploadedFile) {
                $this->deleteDocument($documentPath);
                $documentPath = $this->uploadDocument($files[$index]['document']);
            }

            $payload = [
                'key_points'  => $row['key_points'],
                'description' => $row['description'] ?? null,
                'document'    => $documentPath,
            ];

            if ($task) {
                $task->update($payload);
                $keptTaskIds[] = $task->id;
            } else {
                $newTask = $milestone->tasks()->create($payload);
                $keptTaskIds[] = $newTask->id;
            }
        }

        $removed = $milestone->tasks()->whereNotIn('id', $keptTaskIds)->get();
        foreach ($removed as $task) {
            $this->deleteDocument($task->document);
            $task->delete();
        }
    }

    protected function uploadDocument(UploadedFile $file): string
    {
        $folder = public_path('uploads/tasks');

        if (!File::isDirectory($folder)) {
            File::makeDirectory($folder, 0755, true, true);
        }

        $name = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($folder, $name);

        return 'uploads/tasks/' . $name;
    }

    protected function deleteDocument(?string $relativePath): void
    {
        if (!$relativePath) {
            return;
        }

        $full = public_path($relativePath);

        if (File::exists($full)) {
            File::delete($full);
        }
    }

    protected function notifyStudents(ProjectMilestone $milestone): void
    {
        try {
            $milestone->load('project.teamMembers');

            Notification::send(
                $milestone->project->teamMembers,
                new MilestoneCreatedNotification($milestone, Auth::user()->name)
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Save individual marks (out of 20) for each student into
     * project_marks + marks_distributions (topic = Milestones).
     */
    protected function saveStudentMarks(ProjectMilestone $milestone, array $marks): void
    {
        $studentIds = $milestone->project->teamMembers->pluck('id')->map(fn ($v) => (int) $v)->all();
        $now        = now();

        foreach ($marks as $studentId => $data) {
            // Ignore anyone who isn't a member of this project
            if (! in_array((int) $studentId, $studentIds, true)) {
                continue;
            }

            $projectMark = ProjectMark::updateOrCreate(
                [
                    'project_id' => $milestone->project_id,
                    'student_id' => $studentId,
                ],
                [
                    'supervisor_id' => Auth::id(),
                    'remarks'       => $data['remarks'] ?? null,
                ]
            );

            // marks_distributions has no single primary key, so use the query builder
            $query = DB::table('marks_distributions')
                ->where('project_marks_id', $projectMark->id)
                ->where('topic', 'Milestones');

            $values = [
                'given_marks' => $data['given_marks'],
                'out_of'      => ProjectMark::MARKS_PER_TOPIC,
                'updated_at'  => $now,
            ];

            if ($query->exists()) {
                $query->update($values);
            } else {
                DB::table('marks_distributions')->insert($values + [
                    'project_marks_id' => $projectMark->id,
                    'topic'            => 'Milestones',
                    'created_at'       => $now,
                ]);
            }
        }
    }
}
