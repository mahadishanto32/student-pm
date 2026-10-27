<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presentation;
use App\Models\Project;
use App\Models\User;
use App\Models\ProjectMark;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AdminPresentationController extends Controller
{
    /**
     * Base directory (relative to public/) where presentation files live.
     */
    protected string $uploadDir = 'uploads/presentations';

    /**
     * Display a listing of all presentations.
     */
    public function index(Request $request)
    {
        $query = Presentation::query()->with(['project', 'presenter']);

        // Search
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

        // Filter by project
        if ($projectId = $request->input('project_id')) {
            $query->where('project_id', $projectId);
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $presentations = $query->latest('date_of_presentation')->paginate(15);

        // Data for filter dropdowns
        $projects = Project::orderBy('group_number')->get();

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

        return view('admin.presentations.index', compact('presentations', 'projects', 'marksSummary'));
    }

    /**
     * Display the specified presentation.
     */
    public function show(Presentation $presentation)
    {
        $presentation->load(['project.teamMembers', 'presenter']);

        $students = $presentation->project->teamMembers
            ->where('role', 'student')
            ->values();

        $marks = ProjectMark::with([
                'distributions' => fn ($q) => $q->where('topic', 'Presentation'),
                'supervisor',
            ])
            ->where('project_id', $presentation->project_id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        return view('admin.presentations.show', compact('presentation', 'students', 'marks'));
    }

    /**
     * Show the form for editing the specified presentation.
     */
    public function edit(Presentation $presentation)
    {
        $presentation->load(['project', 'presenter']);

        $projects = Project::orderBy('group_number')->get();
        $users    = User::orderBy('name')->get();

        return view('admin.presentations.edit', compact('presentation', 'projects', 'users'));
    }

    /**
     * Update the specified presentation (admin can edit all fields).
     */
    public function update(Request $request, Presentation $presentation)
    {
        $validated = $request->validate([
            'project_id'            => ['required', 'exists:projects,id'],
            'title'                 => ['nullable', 'string', 'max:255'],
            'done_by'               => ['required', 'exists:users,id'],
            'key_points'            => ['nullable', 'string'],
            'supervisor_feedback'   => ['nullable', 'string'],
            'date_of_presentation'  => ['nullable', 'date'],
            'status'                => ['nullable', 'in:pending,approved,rejected,completed'],
            'presentation_file'     => ['nullable', 'file', 'max:10240'], // max 10MB
        ]);

        // Handle file upload if a new file was provided
        if ($request->hasFile('presentation_file')) {
            // Delete old file if present
            if ($presentation->presentation_file) {
                $oldPath = public_path($this->uploadDir . '/' . $presentation->presentation_file);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }

            $file = $request->file('presentation_file');
            $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $file->move(public_path($this->uploadDir), $filename);

            $validated['presentation_file'] = $filename;
        }

        $presentation->update($validated);

        return redirect()
            ->route('admin.presentations.show', $presentation->id)
            ->with('success', 'Presentation updated successfully.');
    }

    /**
     * Remove the specified presentation from storage.
     */
    public function destroy(Presentation $presentation)
    {
        // Delete the attached file from public/uploads/presentations
        if ($presentation->presentation_file) {
            $path = public_path($this->uploadDir . '/' . $presentation->presentation_file);
            if (File::exists($path)) {
                File::delete($path);
            }
        }

        $presentation->delete();

        return redirect()
            ->route('admin.presentations.index')
            ->with('success', 'Presentation deleted successfully.');
    }
}
