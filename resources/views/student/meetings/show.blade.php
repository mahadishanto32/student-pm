@extends('layouts.app')

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
                        <a href="{{ route('student.meetings.index') }}" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Back to Meetings
                        </a>
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
                                <th>Supervisor</th>
                                <td>{{ $meeting->project->teacher->name ?? '—' }}</td>
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
                                <th>Team Members</th>
                                <td>
                                    @forelse ($meeting->project->teamMembers ?? [] as $member)
                                        <span class="label label-info">{{ $member->name }}</span>
                                    @empty
                                        <span class="text-muted">—</span>
                                    @endforelse
                                </td>
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