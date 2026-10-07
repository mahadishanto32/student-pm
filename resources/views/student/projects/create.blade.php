@extends('layouts.app')

@section('title', 'Create Project')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Create Project</h2>
            </div>
        </div>
    </div>

    <div class="row column1">
        <div class="col-md-12">
            <div class="full white_shadow_bg margin_bottom_30">

                @if (session('success'))
                    <div class="alert alert-success" style="margin: 15px;">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger" style="margin: 15px;">{{ session('error') }}</div>
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

                <form action="{{ route('student.projects.store') }}" method="POST" style="padding: 15px;">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="project_name">Project Name <span class="text-danger">*</span></label>
                                <input type="text" name="project_name" id="project_name"
                                       class="form-control" value="{{ old('project_name') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="project_topic">Project Topic</label>
                                <input type="text" name="project_topic" id="project_topic"
                                       class="form-control" value="{{ old('project_topic') }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="short_overview">Short Overview</label>
                        <textarea name="short_overview" id="short_overview" rows="4"
                                  class="form-control">{{ old('short_overview') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label for="assigned_team_member">Assigned Team Members</label>
                        <select name="assigned_team_member[]" id="assigned_team_member"
                                class="form-control" multiple size="6">
                            @foreach ($members as $member)
                                <option value="{{ $member->id }}"
                                    @selected(in_array($member->id, old('assigned_team_member', [])))>
                                    {{ $member->name }} ({{ $member->email }})
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Hold Ctrl (Cmd) to select multiple members.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_date">Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" id="start_date"
                                       class="form-control" value="{{ old('start_date') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tentative_end_date">Tentative End Date</label>
                                <input type="date" name="tentative_end_date" id="tentative_end_date"
                                       class="form-control" value="{{ old('tentative_end_date') }}">
                            </div>
                        </div>
                    </div>

                    <div class="form-group text-right">
                        <a href="{{ route('student.projects.index') }}" class="btn btn-default">Cancel</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Save Project
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
@endsection