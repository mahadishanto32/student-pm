@extends('teachers.layouts.app')

@section('title', 'Meetings')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Meetings</h2>
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

                <div class="row" style="padding: 15px 15px 0 15px;">
                    <div class="col-md-6">
                        <h4>Meetings of Projects I Supervise</h4>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="{{ route('teachers.meetings.create') }}" class="btn btn-success">
                            <i class="fa fa-plus"></i> New Meeting
                        </a>
                    </div>
                </div>

                <div class="row" style="padding: 15px;">
                    <div class="col-md-4">
                        <form action="{{ route('teachers.meetings.index') }}" method="GET">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control"
                                       placeholder="Search title, description or project"
                                       value="{{ request('search') }}">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="submit"><i class="fa fa-search"></i></button>
                                </span>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <form action="{{ route('teachers.meetings.index') }}" method="GET">
                            <select name="type" class="form-control" onchange="this.form.submit()">
                                <option value="">— All Types —</option>
                                @foreach ($types as $t)
                                    <option value="{{ $t }}" @selected(request('type') === $t)>
                                        {{ ucfirst($t) }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <form action="{{ route('teachers.meetings.index') }}" method="GET">
                            <select name="platform" class="form-control" onchange="this.form.submit()">
                                <option value="">— All Platforms —</option>
                                @foreach ($platforms as $p)
                                    <option value="{{ $p }}" @selected(request('platform') === $p)>
                                        {{ ucfirst($p) }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    <div class="col-md-2">
                        <form action="{{ route('teachers.meetings.index') }}" method="GET">
                            <select name="project_id" class="form-control" onchange="this.form.submit()">
                                <option value="">— All Projects —</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}" @selected((int) request('project_id') === $project->id)>
                                        {{ $project->project_name }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>

                <div class="table-responsive" style="padding: 15px;">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Project</th>
                                <th>Date &amp; Time</th>
                                <th>Type</th>
                                <th>Platform</th>
                                <th>Next Meeting</th>
                                <th style="width: 200px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($meetings as $meeting)
                                <tr>
                                    <td>{{ $loop->iteration + ($meetings->currentPage() - 1) * $meetings->perPage() }}</td>
                                    <td>{{ $meeting->title }}</td>
                                    <td>
                                        @if ($meeting->project)
                                            {{ $meeting->project->project_name }}
                                            <br>
                                            <small class="text-muted">
                                                Group: {{ $meeting->project->group_number ?? '—' }}
                                            </small>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $meeting->meeting_date_and_time?->format('d M Y, H:i') ?? '—' }}
                                    </td>
                                    <td>
                                        @php
                                            $typeBadge = match($meeting->type) {
                                                'present'   => 'label-success',
                                                'completed' => 'label-info',
                                                'upcoming'  => 'label-warning',
                                                default     => 'label-default',
                                            };
                                        @endphp
                                        <span class="label {{ $typeBadge }}">{{ ucfirst($meeting->type) }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $platformBadge = match($meeting->platform) {
                                                'physical' => 'label-primary',
                                                'online'   => 'label-info',
                                                default    => 'label-default',
                                            };
                                        @endphp
                                        <span class="label {{ $platformBadge }}">{{ ucfirst($meeting->platform) }}</span>
                                    </td>
                                    <td>
                                        {{ $meeting->tentative_next_meeting_date_and_time?->format('d M Y, H:i') ?? '—' }}
                                    </td>
                                    <td>
                                        <a href="{{ route('teachers.meetings.show', $meeting->id) }}"
                                           class="btn btn-sm btn-default" title="View">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ route('teachers.meetings.edit', $meeting->id) }}"
                                           class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <form action="{{ route('teachers.meetings.destroy', $meeting->id) }}"
                                              method="POST" style="display:inline-block"
                                              onsubmit="return confirm('Delete this meeting?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger" title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No meetings found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div style="padding: 0 15px 15px 15px;">
                    {{ $meetings->appends(request()->query())->links() }}
                </div>

            </div>
        </div>
    </div>
@endsection