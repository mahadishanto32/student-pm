<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\VideoResume;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class StudentVideoResumeController extends Controller
{
    private const UPLOAD_DIR = 'uploads/video-resumes';

    /** Projects assigned to the logged-in student. */
    private function assignedProjects()
    {
        $userId = auth()->id();

        return Project::whereHas('teamMembers', function ($q) use ($userId) {
            $q->where('users.id', $userId);
        });
    }

    /** Find a video resume only if it belongs to one of the student's projects. */
    private function findOwned($id): VideoResume
    {
        return VideoResume::whereIn('project_id', $this->assignedProjects()->pluck('projects.id'))
            ->with('project')
            ->findOrFail($id);
    }

    private function rules(bool $isUpdate = false): array
    {
        return [
            'project_id' => ['required', 'integer', 'in:' . $this->assignedProjects()->pluck('projects.id')->implode(',')],
            'title'      => ['nullable', 'string', 'max:255'],
            'media_file' => [$isUpdate ? 'nullable' : 'required', 'file', 'mimes:mp4,mov,avi,webm,mkv', 'max:51200'], // 50 MB in KB
        ];
    }

    private function messages(): array
    {
        return [
            'project_id.in'    => 'You can only submit a video resume for your assigned project.',
            'media_file.max'   => 'The video must not be larger than 50 MB.',
            'media_file.mimes' => 'The video must be a file of type: mp4, mov, avi, webm, mkv.',
        ];
    }

    private function uploadVideo($file): string
    {
        $dir = public_path(self::UPLOAD_DIR);
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $filename = time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
        $file->move($dir, $filename);

        return self::UPLOAD_DIR . '/' . $filename;
    }

    private function deleteVideo(?string $path): void
    {
        if ($path && File::exists(public_path($path))) {
            File::delete(public_path($path));
        }
    }

    public function index(Request $request)
    {
        $projects = $this->assignedProjects()->orderBy('project_name')->get();

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

        return view('student.video-resumes.index', compact('videoResumes', 'projects'));
    }

    public function create()
    {
        $projects = $this->assignedProjects()->orderBy('project_name')->get();

        return view('student.video-resumes.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules(), $this->messages());

        $data['media_file'] = $this->uploadVideo($request->file('media_file'));

        VideoResume::create($data);

        return redirect()->route('student.video-resumes.index')
            ->with('success', 'Video resume uploaded successfully.');
    }

    public function show($id)
    {
        $videoResume = $this->findOwned($id);

        return view('student.video-resumes.show', compact('videoResume'));
    }

    public function edit($id)
    {
        $videoResume = $this->findOwned($id);
        $projects    = $this->assignedProjects()->orderBy('project_name')->get();

        return view('student.video-resumes.edit', compact('videoResume', 'projects'));
    }

    public function update(Request $request, $id)
    {
        $videoResume = $this->findOwned($id);

        $data = $request->validate($this->rules(true), $this->messages());

        if ($request->hasFile('media_file')) {
            $this->deleteVideo($videoResume->media_file);
            $data['media_file'] = $this->uploadVideo($request->file('media_file'));
        } else {
            unset($data['media_file']);
        }

        $videoResume->update($data);

        return redirect()->route('student.video-resumes.index')
            ->with('success', 'Video resume updated successfully.');
    }

    public function destroy($id)
    {
        $videoResume = $this->findOwned($id);

        $this->deleteVideo($videoResume->media_file);
        $videoResume->delete();

        return redirect()->route('student.video-resumes.index')
            ->with('success', 'Video resume deleted successfully.');
    }
}
