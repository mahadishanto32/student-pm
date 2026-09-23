@extends('layouts.app')

@section('title', 'Create Milestone')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Create Milestone</h2>
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

                <form action="{{ route('student.milestones.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row" style="padding: 15px;">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Project <span class="text-danger">*</span></label>
                                <select name="project_id" class="form-control" required>
                                    <option value="">— Select Project —</option>
                                    @foreach ($projects as $project)
                                        <option value="{{ $project->id }}"
                                            @selected(old('project_id', $selectedProjectId) == $project->id)>
                                            Group {{ $project->group_number }} — {{ $project->project_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control"
                                       value="{{ old('title') }}" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tentative Time</label>
                                <input type="datetime-local" name="tentative_time" class="form-control"
                                       value="{{ old('tentative_time') }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Status</label>
                                <input type="text" class="form-control" value="Pending" disabled>
                                <small class="text-muted">Status is set by the supervisor.</small>
                            </div>
                        </div>
                    </div>

                    <div class="row" style="padding: 0 15px;">
                        <div class="col-md-12">
                            <h5>Milestone Tasks</h5>
                            <hr>
                        </div>
                    </div>

                    <div id="tasks-container" style="padding: 0 15px;">
                        @php
                            $oldTasks = old('tasks', [['key_points' => '', 'document' => null]]);
                        @endphp
                        @foreach ($oldTasks as $i => $task)
                            <div class="row task-row" style="margin-bottom: 10px; border-bottom: 1px dashed #eee; padding-bottom: 10px;">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Key Points <span class="text-danger">*</span></label>
                                        <textarea name="tasks[{{ $i }}][key_points]" class="form-control" rows="2"
                                                  required>{{ $task['key_points'] ?? '' }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label>Document (image / pdf / docx)</label>
                                        <input type="file" name="tasks[{{ $i }}][document]" class="form-control"
                                               accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx">
                                    </div>
                                </div>
                                <div class="col-md-1" style="padding-top: 28px;">
                                    <button type="button" class="btn btn-danger btn-sm remove-task">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="row" style="padding: 0 15px 15px 15px;">
                        <div class="col-md-12">
                            <button type="button" id="add-task" class="btn btn-default">
                                <i class="fa fa-plus"></i> Add Task
                            </button>
                        </div>
                    </div>

                    <div class="row" style="padding: 0 15px 15px 15px;">
                        <div class="col-md-12 text-right">
                            <a href="{{ route('student.milestones.index') }}" class="btn btn-default">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Milestone</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script>
        (function () {
            const container = document.getElementById('tasks-container');
            const addBtn    = document.getElementById('add-task');

            function reindex() {
                container.querySelectorAll('.task-row').forEach((row, i) => {
                    row.querySelectorAll('input, textarea, select').forEach(el => {
                        const name = el.getAttribute('name');
                        if (!name) return;
                        el.setAttribute('name', name.replace(/tasks\[\d+\]/, `tasks[${i}]`));
                    });
                });
            }

            addBtn.addEventListener('click', () => {
                const i = container.querySelectorAll('.task-row').length;
                const html = `
                    <div class="row task-row" style="margin-bottom: 10px; border-bottom: 1px dashed #eee; padding-bottom: 10px;">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Key Points <span class="text-danger">*</span></label>
                                <textarea name="tasks[${i}][key_points]" class="form-control" rows="2" required></textarea>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Document (image / pdf / docx)</label>
                                <input type="file" name="tasks[${i}][document]" class="form-control"
                                       accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx">
                            </div>
                        </div>
                        <div class="col-md-1" style="padding-top: 28px;">
                            <button type="button" class="btn btn-danger btn-sm remove-task">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    </div>`;
                container.insertAdjacentHTML('beforeend', html);
            });

            container.addEventListener('click', (e) => {
                const btn = e.target.closest('.remove-task');
                if (!btn) return;
                const rows = container.querySelectorAll('.task-row');
                if (rows.length <= 1) {
                    alert('At least one task is required.');
                    return;
                }
                btn.closest('.task-row').remove();
                reindex();
            });
        })();
    </script>
@endsection