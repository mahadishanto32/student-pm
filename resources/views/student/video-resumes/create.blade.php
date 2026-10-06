@extends('layouts.app')

@section('title', 'Add Video Resume')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Add Video Resume</h2>
            </div>
        </div>
    </div>

    <div class="row column1">
        <div class="col-md-12">
            <div class="full white_shadow_bg margin_bottom_30">

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div style="padding: 15px;">
                    <form action="{{ route('student.video-resumes.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group @error('project_id') has-error @enderror">
                            <label for="project_id">Project <span class="text-danger">*</span></label>
                            <select name="project_id" id="project_id" class="form-control" required>
                                <option value="">— Select Project —</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}" @selected(old('project_id') == $project->id)>
                                        {{ $project->project_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('project_id') <span class="help-block">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group @error('title') has-error @enderror">
                            <label for="title">Title</label>
                            <input type="text" name="title" id="title" class="form-control"
                                   value="{{ old('title') }}" maxlength="255">
                            @error('title') <span class="help-block">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group @error('media_file') has-error @enderror">
                            <label for="media_file">Video File <span class="text-danger">*</span></label>
                            <input type="file" name="media_file" id="media_file" class="form-control"
                                   accept="video/mp4,video/quicktime,video/x-msvideo,video/webm,video/x-matroska" required>
                            <small class="text-muted">Allowed: mp4, mov, avi, webm, mkv. Maximum size: 50 MB.</small>
                            @error('media_file') <span class="help-block">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Upload</button>
                            <a href="{{ route('student.video-resumes.index') }}" class="btn btn-default">Cancel</a>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('media_file').addEventListener('change', function () {
        if (this.files[0] && this.files[0].size > 50 * 1024 * 1024) {
            alert('The video must not be larger than 50 MB.');
            this.value = '';
        }
    });
</script>
@endpush
