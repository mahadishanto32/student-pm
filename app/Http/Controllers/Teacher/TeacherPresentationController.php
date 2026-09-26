<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Presentation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherPresentationController extends Controller
{
    /**
     * Display a listing of presentations for projects assigned to this teacher.
     */
    public function index(Request $request)
    {
        $teacherId = Auth::id();

        $query = Presentation::query()
            ->with(['project', 'presenter'])
            ->whereHas('project', function ($q) use ($teacherId) {
                $q->where('assigned_teacher', $teacherId);
            });

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

        // Status filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $presentations = $query->latest('date_of_presentation')->paginate(15);

        return view('teachers.presentations.index', compact('presentations'));
    }

    /**
     * Display the specified presentation.
     */
    public function show(Presentation $presentation)
    {
        $this->authorizeTeacher($presentation);

        $presentation->load(['project', 'presenter']);

        return view('teachers.presentations.show', compact('presentation'));
    }

    /**
     * Show the form for editing the specified presentation.
     */
    public function edit(Presentation $presentation)
    {
        $this->authorizeTeacher($presentation);

        $presentation->load(['project', 'presenter']);

        return view('teachers.presentations.edit', compact('presentation'));
    }

    /**
     * Update the specified presentation (only marks, supervisor_feedback, status).
     */
    public function update(Request $request, Presentation $presentation)
    {
        $this->authorizeTeacher($presentation);

        $validated = $request->validate([
            'marks'               => ['nullable', 'numeric', 'min:0', 'max:100'],
            'supervisor_feedback' => ['nullable', 'string'],
            'status'              => ['nullable', 'in:pending,approved,rejected,completed'],
        ]);

        $presentation->update($validated);

        return redirect()
            ->route('teachers.presentations.show', $presentation->id)
            ->with('success', 'Presentation updated successfully.');
    }

    /**
     * Ensure the authenticated teacher supervises the presentation's project.
     */
    protected function authorizeTeacher(Presentation $presentation): void
    {
        $teacherId = Auth::id();

        $isSupervisor = $presentation->project
            && $presentation->project->supervisor_id === $teacherId;

        abort_unless($isSupervisor, 403, 'You are not assigned to this project.');
    }
}
