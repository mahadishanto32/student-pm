@extends('teachers.layouts.app')

@section('title', 'Bulk Create Meeting')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Bulk Create Meeting</h2>
            </div>
        </div>
    </div>

    <div class="row column1">
        <div class="col-md-12">
            <div class="full white_shadow_bg margin_bottom_30">

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul style="margin-bottom:0;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="alert alert-info" style="margin: 15px;">
                    <i class="fa fa-info-circle"></i>
                    This will create <strong>the same meeting</strong> for each of
                    <strong>{{ $projects->count() }}</strong> assigned project(s).
                    All fields below are shared across every created meeting.
                </div>

                <form action="{{ route('teachers.meetings.bulk-store') }}"
                      method="POST" style="padding: 15px;">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Projects that will receive this meeting</label>
                                <div style="max-height: 180px; overflow-y: auto; border: 1px solid #ddd; padding: 8px; border-radius: 4px;">
                                    @foreach ($projects as $project)
                                        <div style="margin-bottom: 4px;">
                                            <span class="label label-info">
                                                {{ $project->group_number ?? '—' }}
                                            </span>
                                            {{ $project->project_name ?? $project->project_topic }}
                                        </div>
                                    @endforeach
                                </div>
                                <small class="text-muted">
                                    All {{ $projects->count() }} will get a meeting with the same details.
                                </small>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="title" class="form-control"
                                       value="{{ old('title') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="meeting_date_and_time">Date &amp; Time <span class="text-danger">*</span></label>
                                <input type="datetime-local" name="meeting_date_and_time" id="meeting_date_and_time"
                                       class="form-control"
                                       value="{{ old('meeting_date_and_time') }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="platform">Platform <span class="text-danger">*</span></label>
                                <select name="platform" id="platform" class="form-control" required>
                                    <option value="">— Select —</option>
                                    @foreach ($platforms as $p)
                                        <option value="{{ $p }}" @selected(old('platform') === $p)>
                                            {{ ucfirst($p) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="type">Type <span class="text-danger">*</span></label>
                                <select name="type" id="type" class="form-control" required>
                                    <option value="">— Select —</option>
                                    @foreach ($types as $t)
                                        <option value="{{ $t }}" @selected(old('type') === $t)>
                                            {{ ucfirst($t) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tentative_next_meeting_date_and_time">Tentative Next Meeting (optional)</label>
                                <input type="datetime-local"
                                       name="tentative_next_meeting_date_and_time"
                                       id="tentative_next_meeting_date_and_time"
                                       class="form-control"
                                       value="{{ old('tentative_next_meeting_date_and_time') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea name="description" id="description" rows="3"
                                          class="form-control">{{ old('description') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="supervisor_note">Supervisor Note</label>
                                <textarea name="supervisor_note" id="supervisor_note" rows="3"
                                          class="form-control">{{ old('supervisor_note') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-save"></i>
                                Create {{ $projects->count() }} Meeting{{ $projects->count() === 1 ? '' : 's' }}
                            </button>
                            <a href="{{ route('teachers.meetings.index') }}" class="btn btn-default">
                                Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection