@extends('teachers.layouts.app')

@section('title', 'Meeting Details')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Meeting Details</h2>
            </div>
        </div>
    </div>

    <div class="row column1">
        <div class="col-md-12">
            <div class="full white_shadow_bg margin_bottom_30">

                <div class="row" style="padding: 15px 15px 0 15px;">
                    <div class="col-md-6">
                        <h4>{{ $meeting->title }}</h4>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="{{ route('teachers.meetings.index') }}" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                        <a href="{{ route('teachers.meetings.edit', $meeting->id) }}" class="btn btn-primary">
                            <i class="fa fa-pencil"></i> Edit
                        </a>
                        <form action="{{ route('teachers.meetings.destroy', $meeting->id) }}"
                              method="POST" style="display:inline-block"
                              onsubmit="return confirm('Delete this meeting?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger" title="Delete">
                                <i class="fa fa-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>

                <div style="padding: 15px;">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th style="width: 250px;">ID</th>
                                <td>{{ $meeting->id }}</td>
                            </tr>
                            <tr>
                                <th>Title</th>
                                <td>{{ $meeting->title }}</td>
                            </tr>
                            <tr>
                                <th>Project</th>
                                <td>
                                    @if ($meeting->project)
                                        {{ $meeting->project->project_name }}
                                        @if ($meeting->project->group_number)
                                            <span class="label label-info">Group: {{ $meeting->project->group_number }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Meeting Date &amp; Time</th>
                                <td>{{ $meeting->meeting_date_and_time?->format('d M Y, H:i') ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Tentative Next Meeting</th>
                                <td>{{ $meeting->tentative_next_meeting_date_and_time?->format('d M Y, H:i') ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Type</th>
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
                            </tr>
                            <tr>
                                <th>Platform</th>
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
                            </tr>
                            <tr>
                                <th>Description</th>
                                <td>{!! nl2br(e($meeting->description ?? '—')) !!}</td>
                            </tr>
                            <tr>
                                <th>Supervisor Note</th>
                                <td>{!! nl2br(e($meeting->supervisor_note ?? '—')) !!}</td>
                            </tr>
                            <tr>
                                <th>Created At</th>
                                <td>{{ $meeting->created_at?->format('d M Y, H:i') ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Updated At</th>
                                <td>{{ $meeting->updated_at?->format('d M Y, H:i') ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
@endsection