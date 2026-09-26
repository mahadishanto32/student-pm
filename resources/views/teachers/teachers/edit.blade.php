@extends('teachers.layouts.app')

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
                        <a href="{{ route('teachers.presentations.index') }}" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to List
                        </a>
                        <a href="{{ route('teachers.presentations.show', $presentation->id) }}"
                           class="btn btn-default btn-sm">
                            <i class="fa fa-eye"></i> View
                        </a>
                    </div>
                </div>

                <form action="{{ route('teachers.presentations.update', $presentation->id) }}"
                      method="POST" enctype="multipart/form-data" style="padding: 15px;">
                    @csrf
                    @method('PUT')

                    {{-- Read-only context info --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Project</label>
                                <input type="text" class="form-control"
                                       value="{{ $presentation->project->project_name ?? '—' }}" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" class="form-control"
                                       value="{{ $presentation->title ?? '—' }}" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Done By</label>
                                <input type="text" class="form-control"
                                       value="{{ $presentation->presenter->name ?? '—' }}" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date of Presentation</label>
                                <input type="text" class="form-control"
                                       value="{{ $presentation->date_of_presentation?->format('d M Y') ?? '—' }}" disabled>
                            </div>
                        </div>
                    </div>

                    <hr>

                    {{-- Editable fields --}}
                    <div class="row">
                        <div class="col-md-6">
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
                        <div class="col-md-6">
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
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="supervisor_feedback">Supervisor Feedback</label>
                                <textarea name="supervisor_feedback" id="supervisor_feedback" rows="5"
                                          class="form-control @error('supervisor_feedback') is-invalid @enderror"
                                          placeholder="Write feedback...">{{ old('supervisor_feedback', $presentation->supervisor_feedback) }}</textarea>
                                @error('supervisor_feedback')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Presentation file (read-only reference) --}}
                    @if ($presentation->presentation_file)
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Current Presentation File</label><br>
                                    <a href="{{ asset('uploads/presentations/' . $presentation->presentation_file) }}"
                                       target="_blank" class="btn btn-sm btn-default">
                                        <i class="fa fa-file"></i> View File
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-12 text-right">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Update
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection
