@extends('layouts.app')

@section('title', 'Update Milestone Tasks')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Update Milestone Tasks</h2>
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

                <form action="{{ route('student.milestones.update', $milestone->id) }}"
                      method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    {{-- Read-only milestone info --}}
                    <div class="row" style="padding: 15px;">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Project</label>
                                <input type="text" class="form-control" disabled
                                       value="Group {{ $milestone->project->group_number ?? '—' }} — {{ $milestone->project->project_name ?? '—' }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Title</label>
                                <input type="text" class="form-control" disabled
                                       value="{{ $milestone->title }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tentative Time</label>
                                <input type="text" class="form-control" disabled
                                       value="{{ $milestone->tentative_time?->format('d M Y, h:i A') ?? '—' }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status</label>
                                <input type="text" class="form-control" disabled
                                       value="{{ ucwords(str_replace('_', ' ', $milestone->status)) }}">
                                <small class="text-muted">Status is set by the supervisor.</small>
                            </div>
                        </div>

                        @if ($milestone->supervisor_note)
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Supervisor Note</label>
                                    <textarea class="form-control" rows="2" disabled>{{ $milestone->supervisor_note }}</textarea>
                                    <small class="text-muted">Supervisor note cannot be edited by students.</small>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="row" style="padding: 0 15px;">
                        <div class="col-md-12">
                            <h5>Milestone Tasks</h5>
                            <small class="text-muted">You can update the description and document for each task. Key points are set by the supervisor.</small>
                            <hr>
                        </div>
                    </div>

                    <div id="tasks-container" style="padding: 0 15px;">
                        @php
                            $oldTasks = old('tasks');
                            $tasks = $oldTasks ?: $milestone->tasks->map(fn ($t) => [
                                'id'              => $t->id,
                                'key_points'      => $t->key_points,
                                'description'     => $t->description,
                                'document'        => $t->document,
                                'remove_document' => false,
                            ])->toArray();
                        @endphp

                        @foreach ($tasks as $i => $task)
                            <div class="row task-row" style="margin-bottom: 10px; border-bottom: 1px dashed #eee; padding-bottom: 10px;">
                                <input type="hidden" name="tasks[{{ $i }}][id]" value="{{ $task['id'] ?? '' }}">

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Key Points</label>
                                        <textarea class="form-control" rows="3" disabled>{{ $task['key_points'] ?? '' }}</textarea>
                                        <small class="text-muted">Key points are set by the supervisor.</small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea name="tasks[{{ $i }}][description]" class="form-control"
                                                  rows="3" placeholder="Describe your progress for this task...">{{ $task['description'] ?? '' }}</textarea>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Document (image / pdf / docx)</label>
                                        <input type="file" name="tasks[{{ $i }}][document]" class="form-control"
                                               accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx">

                                        @if (!empty($task['document']))
                                            <div class="checkbox" style="margin-top: 5px;">
                                                <label>
                                                    <input type="checkbox"
                                                           name="tasks[{{ $i }}][remove_document]"
                                                           value="1"> Remove existing document
                                                </label>
                                                <a href="{{ asset($task['document']) }}" target="_blank">
                                                    <i class="fa fa-file"></i> View current file
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="row" style="padding: 0 15px 15px 15px;">
                        <div class="col-md-12 text-right">
                            <a href="{{ route('student.milestones.show', $milestone->id) }}" class="btn btn-default">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection