<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Presentation;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class StudentPresentationController extends Controller
{
    /**
     * Only allow students to see/manage presentations of their own projects.
     */
    private function userProjectIds()
    {
        return Project::whereHas('teamMembers', function ($q) {
                $q->where('users.id', Auth::id());
            })
            ->pluck('id')
            ->toArray();
    }

    public function index(Request $request)
    {
        $projectIds = $this->userProjectIds();

        $query = Presentation::with(['project', 'presenter'])
            ->whereIn('project_id', $projectIds);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('key_points', 'like', "%{$search}%")
                  ->orWhereHas('project', fn ($p) => $p->where('project_name', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($projectId = $request->input('project_id')) {
            $query->where('project_id', $projectId);
        }

        $presentations = $query->latest('date_of_presentation')->paginate(10)->withQueryString();

        $projects = Project::whereIn('id', $projectIds)->get();
        $statuses = ['pending', 'completed', 'approved', 'rejected'];

        return view('student.presentations.index', compact('presentations', 'projects', 'statuses'));
    }

    public function create()
    {
        $projectIds = $this->userProjectIds();
        $projects   = Project::whereIn('id', $projectIds)->get();

        return view('student.presentations.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $projectIds = $this->userProjectIds();

        $validated = $request->validate([
            'project_id'           => 'required|integer|in:' . implode(',', $projectIds),
            'title'                => 'required|string|max:255',
            'key_points'           => 'nullable|string',
            'date_of_presentation' => 'required|date',
            'presentation_file'    => 'nullable|file|mimes:pdf,ppt,pptx,doc,docx,zip|max:10240',
        ]);

        $validated['done_by'] = Auth::id();
        $validated['status']  = 'pending';

        if ($request->hasFile('presentation_file')) {
            $validated['presentation_file'] = $this->uploadFile($request->file('presentation_file'));
        }

        Presentation::create($validated);

        return redirect()
            ->route('student.presentations.index')
            ->with('success', 'Presentation created successfully.');
    }

    public function show(Presentation $presentation)
    {
        $this->authorizeOwnership($presentation);

        $presentation->load(['project', 'presenter']);

        return view('student.presentations.show', compact('presentation'));
    }

    public function edit(Presentation $presentation)
    {
        $this->authorizeOwnership($presentation);

        $projectIds = $this->userProjectIds();
        $projects   = Project::whereIn('id', $projectIds)->get();

        return view('student.presentations.edit', compact('presentation', 'projects'));
    }

    public function update(Request $request, Presentation $presentation)
    {
        $this->authorizeOwnership($presentation);

        $projectIds = $this->userProjectIds();

        $validated = $request->validate([
            'project_id'           => 'required|integer|in:' . implode(',', $projectIds),
            'title'                => 'required|string|max:255',
            'key_points'           => 'nullable|string',
            'date_of_presentation' => 'required|date',
            'presentation_file'    => 'nullable|file|mimes:pdf,ppt,pptx,doc,docx,zip|max:10240',
        ]);

        if ($request->hasFile('presentation_file')) {
            $this->deleteFile($presentation->presentation_file);
            $validated['presentation_file'] = $this->uploadFile($request->file('presentation_file'));
        }

        // Marks, supervisor_feedback, status are NOT updated by student.
        $presentation->update($validated);

        return redirect()
            ->route('student.presentations.index')
            ->with('success', 'Presentation updated successfully.');
    }

    public function destroy(Presentation $presentation)
    {
        $this->authorizeOwnership($presentation);

        $this->deleteFile($presentation->presentation_file);
        $presentation->delete();

        return redirect()
            ->route('student.presentations.index')
            ->with('success', 'Presentation deleted successfully.');
    }

    /* ------------------------------------------------------------------ */
    /* Helpers                                                            */
    /* ------------------------------------------------------------------ */

    private function authorizeOwnership(Presentation $presentation): void
    {
        if (!in_array($presentation->project_id, $this->userProjectIds(), true)) {
            abort(403, 'You are not allowed to access this presentation.');
        }
    }

    private function uploadFile($file): string
    {
        $destination = public_path('uploads/presentations');

        if (!File::exists($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($destination, $filename);

        return 'uploads/presentations/' . $filename;
    }

    private function deleteFile(?string $relativePath): void
    {
        if ($relativePath && File::exists(public_path($relativePath))) {
            File::delete(public_path($relativePath));
        }
    }
}