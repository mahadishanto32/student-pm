@extends('layouts.app')

@section('title', 'Project Details')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Project Details — {{ $project->group_number }}</h2>
            </div>
        </div>
    </div>

    <div class="row column1">
        <div class="col-md-12">
            <div class="full white_shadow_bg margin_bottom_30">

                @php
                    $badge = match($project->status) {
                        'approved'  => 'label-success',
                        'pending'   => 'label-warning',
                        'working'   => 'label-primary',
                        'completed' => 'label-info',
                        'rejected'  => 'label-danger',
                        'cancelled' => 'label-default',
                        default     => 'label-default',
                    };
                @endphp

                <div class="row" style="padding: 15px 15px 0 15px;">
                    <div class="col-md-6">
                        <h4>{{ $project->project_name ?? 'Untitled Project' }}</h4>
                    </div>
                    <div class="col-md-6 text-right">
                        <span class="label {{ $badge }}" style="font-size: 13px;">
                            {{ ucfirst($project->status) }}
                        </span>
                    </div>
                </div>

                <div style="padding: 15px;">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th style="width: 220px;">Group Number</th>
                                <td>{{ $project->group_number }}</td>
                            </tr>
                            <tr>
                                <th>Project Name</th>
                                <td>{{ $project->project_name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Project Topic</th>
                                <td>{{ $project->project_topic ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Short Overview</th>
                                <td>{!! nl2br(e($project->short_overview ?? '—')) !!}</td>
                            </tr>
                            <tr>
                                <th>Assigned Teacher</th>
                                <td>
                                    @if ($project->teacher)
                                        {{ $project->teacher->name }}
                                        <small class="text-muted">({{ $project->teacher->email }})</small>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Assigned Team Members</th>
                                <td>
                                    @forelse ($project->teamMembers as $member)
                                        <span class="label label-info" style="display:inline-block; margin: 2px;">
                                            {{ $member->name }}
                                        </span>
                                    @empty
                                        <span class="text-muted">No members assigned.</span>
                                    @endforelse

                                    @foreach ($project->partnerApprovals()->pending()->with('user')->get() as $pa)
                                        <span class="label label-warning" style="display:inline-block; margin: 2px;">
                                            {{ $pa->user->name }} (awaiting approval)
                                        </span>
                                    @endforeach
                                </td>
                            </tr>
                            <tr>
                                <th>Start Date</th>
                                <td>{{ $project->start_date?->format('d M Y') }}</td>
                            </tr>
                            <tr>
                                <th>Tentative End Date</th>
                                <td>{{ $project->tentative_end_date?->format('d M Y') ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Created At</th>
                                <td>{{ $project->created_at?->format('d M Y, h:i A') }}</td>
                            </tr>
                            <tr>
                                <th>Updated At</th>
                                <td>{{ $project->updated_at?->format('d M Y, h:i A') }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="text-right">
                        <a href="{{ route('student.projects.index') }}" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                        @unless ($myApproval)
                            <a href="{{ route('student.projects.edit', $project->id) }}" class="btn btn-primary">
                                <i class="fa fa-pencil"></i> Edit
                            </a>
                        @endunless
                    </div>
                </div>

            </div>
            @if ($myApproval)
                <div class="alert alert-warning" style="margin: 15px;">
                    <strong>Team invitation:</strong>
                    You have been assigned to this project by {{ $myApproval->inviter->name ?? 'a student' }}.
                    Do you want to join?

                    <form action="{{ route('student.partner-approvals.respond', $myApproval->id) }}"
                        method="POST" style="display:inline-block; margin-left: 15px;">
                        @csrf
                        @method('PATCH')
                        <button type="submit" name="approve" value="Yes" class="btn btn-success btn-sm">
                            <i class="fa fa-check"></i> Accept
                        </button>
                        <button type="submit" name="approve" value="No" class="btn btn-danger btn-sm"
                                onclick="return confirm('Decline this invitation?')">
                            <i class="fa fa-times"></i> Decline
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection