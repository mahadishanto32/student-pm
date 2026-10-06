<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\VideoResume;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AdminVideoResumeController extends Controller
{
    private const UPLOAD_DIR = 'uploads/video-resumes';

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
        $projects = Project::orderBy('group_number')->orderBy('project_name')->get();

        $videoResumes = VideoResume::with('project')
            ->when($request->filled('project_id'), fn ($q) => $q->where('project_id', $request->project_id))
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->search;
                $q->where(function ($q) use ($s) {
                    $q->where('title', 'like', "%{$s}%")
                      ->orWhereHas('project', function ($p) use ($s) {
                          $p->where('project_name', 'like', "%{$s}%")
                            ->orWhere('group_number', 'like', "%{$s}%");
                      });
                });
            })
            ->latest()
            ->paginate(10);

        return view('admin.video-resumes.index', compact('videoResumes', 'projects'));
    }

    public function show($id)
    {
        $videoResume = VideoResume::with(['project.teacher', 'project.teamMembers'])->findOrFail($id);

        return view('admin.video-resumes.show', compact('videoResume'));
    }

    public function edit($id)
    {
        $videoResume = VideoResume::with('project')->findOrFail($id);
        $projects    = Project::orderBy('group_number')->orderBy('project_name')->get();

        return view('admin.video-resumes.edit', compact('videoResume', 'projects'));
    }

    public function update(Request $request, $id)
    {
        $videoResume = VideoResume::findOrFail($id);

        $data = $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'title'      => ['nullable', 'string', 'max:255'],
            'media_file' => ['nullable', 'file', 'mimes:mp4,mov,avi,webm,mkv', 'max:51200'], // 50 MB
        ], [
            'media_file.max'   => 'The video must not be larger than 50 MB.',
            'media_file.mimes' => 'The video must be a file of type: mp4, mov, avi, webm, mkv.',
        ]);

        if ($request->hasFile('media_file')) {
            $this->deleteVideo($videoResume->media_file);
            $data['media_file'] = $this->uploadVideo($request->file('media_file'));
        } else {
            unset($data['media_file']);
        }

        $videoResume->update($data);

        return redirect()->route('admin.video-resumes.index')
            ->with('success', 'Video resume updated successfully.');
    }
}
