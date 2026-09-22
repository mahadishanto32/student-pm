@extends('layouts.app')

@section('title', 'Edit Project')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Edit Project — {{ $project->group_number }}</h2>
            </div>
        </div>
    </div>

    <div class="row column1">
        <div class="col-md-12">
            <div class="full white_shadow_bg margin_bottom_30">

                @if ($errors->any())
                    <div class="alert alert-danger" style="margin: 15px;">
                        <ul style="margin-bottom: 0;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="alert alert-info" style="margin: 15px;">
                    <i class="fa fa-info-circle"></i>
                    You can only edit <strong>Project Name</strong>, <strong>Project Topic</strong> and <strong>Short Overview</strong>.
                    All other fields are managed by the admin / teacher.
                </div>

                <form action="{{ route('student.projects.update', $project->id) }}" method="POST" style="padding: 15px;">
                    @csrf
                    @method('PUT')

                    {{-- ---------- Read-only block ---------- --}}
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="group_number_display">Group Number</label>
                                <input type="text" id="group_number_display"
                                       class="form-control" value="{{ $project->group_number }}" disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="assigned_teacher_display">Assigned Teacher</label>
                                <input type="text" id="assigned_teacher_display"
                                       class="form-control"
                                       value="{{ $project->teacher->name ?? '—' }}" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Assigned Team Members</label>
                        <div style="border: 1px solid #ddd; border-radius: 4px; padding: 8px; min-height: 40px; background: #f9f9f9;">
                            @forelse ($project->teamMembers as $member)
                                <span class="label label-info" style="display:inline-block; margin: 2px;">
                                    {{ $member->name }}
                                </span>
                            @empty
                                <span class="text-muted">No members assigned.</span>
                            @endforelse
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Start Date</label>
                                <input type="text" class="form-control"
                                       value="{{ $project->start_date?->format('d M Y') }}" disabled>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tentative End Date</label>
                                <input type="text" class="form-control"
                                       value="{{ $project->tentative_end_date?->format('d M Y') ?? '—' }}" disabled>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Status</label>
                                <input type="text" class="form-control"
                                       value="{{ ucfirst($project->status) }}" disabled>
                            </div>
                        </div>
                    </div>

                    {{-- ---------- Editable fields ---------- --}}
                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="project_name">Project Name</label>
                                <input type="text" name="project_name" id="project_name"
                                       class="form-control"
                                       value="{{ old('project_name', $project->project_name) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="project_topic">Project Topic</label>
                                <input type="text" name="project_topic" id="project_topic"
                                       class="form-control"
                                       value="{{ old('project_topic', $project->project_topic) }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="short_overview">Short Overview</label>
                        <textarea name="short_overview" id="short_overview" rows="4"
                                  class="form-control">{{ old('short_overview', $project->short_overview) }}</textarea>
                    </div>

                    <div class="form-group text-right">
                        <a href="{{ route('student.projects.index') }}" class="btn btn-default">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Update Project
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
@endsection