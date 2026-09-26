@extends('admin.layouts.app')

@section('title', 'Edit Presentation')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Edit Presentation</h2>
            </div>
        </div>
    </div>

    <div class="row column1">
        <div class="col-md-12">
            <div class="full white_shadow_bg margin_bottom_30">

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger" style="margin: 15px;">
                        <ul style="margin-bottom: 0;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row" style="padding: 15px;">
                    <div class="col-md-12">
                        <a href="{{ route('admin.presentations.index') }}" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to List
                        </a>
                        <a href="{{ route('admin.presentations.show', $presentation->id) }}"
                           class="btn btn-default btn-sm">
                            <i class="fa fa-eye"></i> View
                        </a>
                    </div>
                </div>

                <form action="{{ route('admin.presentations.update', $presentation->id) }}"
                      method="POST" enctype="multipart/form-data" style="padding: 15px;">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="project_id">Project <span class="text-danger">*</span></label>
                                <select name="project_id" id="project_id"
                                        class="form-control @error('project_id') is-invalid @enderror">
                                    <option value="">— Select Project —</option>
                                    @foreach ($projects as $project)
                                        <option value="{{ $project->id }}"
                                            @selected(old('project_id', $presentation->project_id) == $project->id)>
                                            Group {{ $project->group_number }} — {{ $project->project_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('project_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Title</label>
                                <input type="text" name="title" id="title"
                                       class="form-control @error('title') is-invalid @enderror"
                                       value="{{ old('title', $presentation->title) }}">
                                @error('title')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="done_by">Done By (Presenter) <span class="text-danger">*</span></label>
                                <select name="done_by" id="done_by"
                                        class="form-control @error('done_by') is-invalid @enderror">
                                    <option value="">— Select User —</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}"
                                            @selected(old('done_by', $presentation->done_by) == $user->id)>
                                            {{ $user->name }} ({{ $user->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('done_by')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date_of_presentation">Date of Presentation</label>
                                <input type="date" name="date_of_presentation" id="date_of_presentation"
                                       class="form-control @error('date_of_presentation') is-invalid @enderror"
                                       value="{{ old('date_of_presentation', $presentation->date_of_presentation?->format('Y-m-d')) }}">
                                @error('date_of_presentation')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="key_points">Key Points</label>
                                <textarea name="key_points" id="key_points" rows="4"
                                          class="form-control @error('key_points') is-invalid @enderror"
                                          placeholder="Key points of the presentation...">{{ old('key_points', $presentation->key_points) }}</textarea>
                                @error('key_points')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="supervisor_feedback">Supervisor Feedback</label>
                                <textarea name="supervisor_feedback" id="supervisor_feedback" rows="4"
                                          class="form-control @error('supervisor_feedback') is-invalid @enderror"
                                          placeholder="Write feedback...">{{ old('supervisor_feedback', $presentation->supervisor_feedback) }}</textarea>
                                @error('supervisor_feedback')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="marks">Marks</label>
                                <input type="number" step="0.01" min="0" max="100"
                                       name="marks" id="marks"
                                       class="form-control @error('marks') is-invalid @enderror"
                                       value="{{ old('marks', $presentation->marks) }}">
                                @error('marks')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select name="status" id="status"
                                        class="form-control @error('status') is-invalid @enderror">
                                    <option value="">— Select Status —</option>
                                    @foreach (['pending','approved','rejected','completed'] as $s)
                                        <option value="{{ $s }}"
                                            @selected(old('status', $presentation->status) === $s)>
                                            {{ ucfirst($s) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="presentation_file">Presentation File</label>
                                <input type="file" name="presentation_file" id="presentation_file"
                                       class="form-control @error('presentation_file') is-invalid @enderror">
                                @error('presentation_file')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                @if ($presentation->presentation_file)
                                    <small class="text-muted d-block mt-1">
                                        Current:
                                        <a href="{{ asset('' . $presentation->presentation_file) }}"
                                           target="_blank">
                                            {{ $presentation->presentation_file }}
                                        </a>
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 text-right">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Update Presentation
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection
