@extends('teachers.layouts.app')

@section('title', 'Milestone Details')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Milestone Details</h2>
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

                <div class="row" style="padding: 15px;">
                    <div class="col-md-8">
                        <h4>{{ $milestone->title }}</h4>
                        <p class="text-muted" style="margin-bottom: 0;">
                            Project:
                            <strong>
                                Group {{ $milestone->project->group_number ?? '—' }} —
                                {{ $milestone->project->project_name ?? '—' }}
                            </strong>
                        </p>
                    </div>
                    <div class="col-md-4 text-right">
                        <a href="{{ route('teachers.milestones.index') }}" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                        <a href="{{ route('teachers.milestones.edit', $milestone->id) }}" class="btn btn-primary">
                            <i class="fa fa-pencil"></i> Review
                        </a>
                        <form action="{{ route('teachers.milestones.destroy', $milestone->id) }}"
                              method="POST" style="display:inline-block;"
                              onsubmit="return confirm('Delete this milestone and all its tasks?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger"><i class="fa fa-trash"></i> Delete</button>
                        </form>
                    </div>
                </div>

                <div class="row" style="padding: 0 15px 15px 15px;">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Tentative Time</label>
                            <p>{{ $milestone->tentative_time?->format('d M Y, h:i A') ?? '—' }}</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Status</label>
                            <p>
                                @php
                                    $badge = match($milestone->status) {
                                        'pending'         => 'label-warning',
                                        'need_correction' => 'label-info',
                                        'completed'       => 'label-success',
                                        'rejected'        => 'label-danger',
                                        default           => 'label-default',
                                    };
                                @endphp
                                <span class="label {{ $badge }}">
                                    {{ ucwords(str_replace('_', ' ', $milestone->status)) }}
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Done By</label>
                            <p>{{ $milestone->doneBy->name ?? '—' }}</p>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Supervisor Note</label>
                            @if ($milestone->supervisor_note)
                                <div class="alert alert-info" style="margin-bottom: 0;">
                                    {{ $milestone->supervisor_note }}
                                </div>
                            @else
                                <p class="text-muted">No note added yet.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row" style="padding: 0 15px 15px 15px;">
                    <div class="col-md-12">
                        <h5>Milestone Tasks ({{ $milestone->tasks->count() }})</h5>
                        <hr>
                    </div>
                </div>

                <div class="table-responsive" style="padding: 0 15px 15px 15px;">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 60px;">#</th>
                                <th>Key Points</th>
                                <th style="width: 200px;">Document</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($milestone->tasks as $task)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $task->key_points }}</td>
                                    <td>
                                        @if ($task->document)
                                            <a href="{{ asset($task->document) }}" target="_blank"
                                               class="btn btn-sm btn-default">
                                                <i class="fa fa-download"></i> Open
                                            </a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No tasks added yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
@endsection