@extends('layouts.app')

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

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row" style="padding: 15px;">
                    <div class="col-md-12">
                        <form action="{{ route('student.presentations.update', $presentation->id) }}"
                              method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="form-group">
                                <label for="project_id">Project <span class="text-danger">*</span></label>
                                <select name="project_id" id="project_id" class="form-control" required>
                                    <option value="">— Select Project —</option>
                                    @foreach ($projects as $project)
                                        <option value="{{ $project->id }}"
                                            @selected(old('project_id', $presentation->project_id) == $project->id)>
                                            {{ $project->project_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="title">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="title" class="form-control"
                                       value="{{ old('title', $presentation->title) }}" required>
                            </div>

                            <div class="form-group">
                                <label for="key_points">Key Points</label>
                                <textarea name="key_points" id="key_points" rows="4"
                                          class="form-control">{{ old('key_points', $presentation->key_points) }}</textarea>
                            </div>

                            <div class="form-group">
                                <label for="date_of_presentation">Date of Presentation <span class="text-danger">*</span></label>
                                <input type="date" name="date_of_presentation" id="date_of_presentation"
                                       class="form-control"
                                       value="{{ old('date_of_presentation', $presentation->date_of_presentation?->format('Y-m-d')) }}"
                                       required>
                            </div>

                            <div class="form-group">
                                <label for="presentation_file">Presentation File</label>
                                <input type="file" name="presentation_file" id="presentation_file"
                                       class="form-control"
                                       accept=".pdf,.ppt,.pptx,.doc,.docx,.zip">
                                <small class="text-muted">
                                    Allowed: pdf, ppt, pptx, doc, docx, zip (max 10 MB).
                                    Leave empty to keep the current file.
                                </small>

                                @if ($presentation->presentation_file)
                                    <div class="mt-2">
                                        <a href="{{ asset($presentation->presentation_file) }}"
                                           target="_blank" class="btn btn-xs btn-default">
                                            <i class="fa fa-download"></i> Current file
                                        </a>
                                    </div>
                                @endif
                            </div>

                            <hr>

                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Update
                            </button>
                            <a href="{{ route('student.presentations.index') }}" class="btn btn-default">
                                Cancel
                            </a>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection