@extends('admin.layouts.app')

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

                <form action="{{ route('admin.projects.update', $project->id) }}" method="POST" style="padding: 15px;">
                    @csrf
                    @method('PUT')

                    @php
                        $selectedMembers = old('assigned_team_member', $project->teamMembers->pluck('id')->toArray());
                    @endphp

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="group_number">Group Number <span class="text-danger">*</span></label>
                                <input type="text" name="group_number" id="group_number"
                                       class="form-control"
                                       value="{{ old('group_number', $project->group_number) }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="project_name">Project Name</label>
                                <input type="text" name="project_name" id="project_name"
                                       class="form-control"
                                       value="{{ old('project_name', $project->project_name) }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="project_topic">Project Topic</label>
                                <input type="text" name="project_topic" id="project_topic"
                                       class="form-control"
                                       value="{{ old('project_topic', $project->project_topic) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="assigned_teacher">Assigned Teacher</label>
                                <select name="assigned_teacher" id="assigned_teacher" class="form-control">
                                    <option value="">— Select Teacher —</option>
                                    @foreach ($teachers as $teacher)
                                        <option value="{{ $teacher->id }}"
                                            @selected(old('assigned_teacher', $project->assigned_teacher) == $teacher->id)>
                                            {{ $teacher->name }} ({{ $teacher->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="short_overview">Short Overview</label>
                        <textarea name="short_overview" id="short_overview" rows="4"
                                  class="form-control">{{ old('short_overview', $project->short_overview) }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="assigned_team_member">Assigned Team Members</label>
                        <select name="assigned_team_member[]" id="assigned_team_member"
                                class="form-control" multiple size="6">
                            @foreach ($members as $member)
                                <option value="{{ $member->id }}"
                                    @selected(in_array($member->id, $selectedMembers))>
                                    {{ $member->name }} ({{ $member->email }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Hold Ctrl (Cmd) to select multiple members.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="start_date">Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" id="start_date"
                                       class="form-control"
                                       value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="tentative_end_date">Tentative End Date</label>
                                <input type="date" name="tentative_end_date" id="tentative_end_date"
                                       class="form-control"
                                       value="{{ old('tentative_end_date', $project->tentative_end_date?->format('Y-m-d')) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="status">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-control" required>
                                    @foreach (['approved','pending','working','completed','rejected','cancelled'] as $s)
                                        <option value="{{ $s }}"
                                            @selected(old('status', $project->status) === $s)>
                                            {{ ucfirst($s) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group text-right">
                        <a href="{{ route('admin.projects.index') }}" class="btn btn-default">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Update Project
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
@endsection