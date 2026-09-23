@extends('teachers.layouts.app')

@section('title', 'Review Milestone')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Review Milestone</h2>
            </div>
        </div>
    </div>

    <div class="row column1">
        <div class="col-md-12">
            <div class="full white_shadow_bg margin_bottom_30">

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('teachers.milestones.update', $milestone->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Read-only milestone info --}}
                    <div class="row" style="padding: 15px;">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Project</label>
                                <input type="text" class="form-control"
                                       value="Group {{ $milestone->project->group_number ?? '—' }} — {{ $milestone->project->project_name ?? '—' }}"
                                       disabled>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" class="form-control" value="{{ $milestone->title }}" disabled>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tentative Time</label>
                                <input type="text" class="form-control"
                                       value="{{ $milestone->tentative_time?->format('d M Y, h:i A') ?? '—' }}"
                                       disabled>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Done By</label>
                                <input type="text" class="form-control"
                                       value="{{ $milestone->doneBy->name ?? '—' }}" disabled>
                            </div>
                        </div>
                    </div>

                    {{-- Read-only tasks --}}
                    <div class="row" style="padding: 0 15px;">
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

                    {{-- Editable fields: status + supervisor_note --}}
                    <div class="row" style="padding: 15px;">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-control" required>
                                    @foreach ([
                                        'pending'         => 'Pending',
                                        'need_correction' => 'Need Correction',
                                        'completed'       => 'Completed',
                                        'rejected'        => 'Rejected',
                                    ] as $value => $label)
                                        <option value="{{ $value }}"
                                            @selected(old('status', $milestone->status) === $value)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Supervisor Note</label>
                                <textarea name="supervisor_note" class="form-control" rows="4"
                                          placeholder="Add feedback for the students...">{{ old('supervisor_note', $milestone->supervisor_note) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row" style="padding: 0 15px 15px 15px;">
                        <div class="col-md-12 text-right">
                            <a href="{{ route('teachers.milestones.show', $milestone->id) }}"
                               class="btn btn-default">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Save Review
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection