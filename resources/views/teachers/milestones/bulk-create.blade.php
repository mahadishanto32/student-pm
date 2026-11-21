@extends('teachers.layouts.app')

@section('title', 'Bulk Create Milestone')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Bulk Create Milestone</h2>
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
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="alert alert-info" style="margin: 15px;">
                    This milestone will be created in <strong>all {{ $projects->count() }}</strong>
                    of your assigned projects:
                    <ul style="margin: 8px 0 0 0;">
                        @foreach ($projects as $project)
                            <li>Group {{ $project->group_number }} — {{ $project->project_name }}</li>
                        @endforeach
                    </ul>
                </div>

                <form action="{{ route('teachers.milestones.bulk-store') }}" method="POST" enctype="multipart/form-data"
                      onsubmit="return confirm('Create this milestone in all {{ $projects->count() }} assigned projects?');">
                    @csrf

                    <div class="row" style="padding: 15px;">
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

                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Supervisor Note</label>
                                <textarea name="supervisor_note" class="form-control" rows="3"
                                          placeholder="Optional note for the students">{{ old('supervisor_note') }}</textarea>
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
                            $oldTasks = old('tasks', [['key_points' => '']]);
                        @endphp
                        @foreach ($oldTasks as $i => $task)
                            <div class="row task-row" style="margin-bottom: 10px; border-bottom: 1px dashed #eee; padding-bottom: 10px;">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Key Points <span class="text-danger">*</span></label>
                                        <textarea name="tasks[{{ $i }}][key_points]" class="form-control" rows="3"
                                                  required>{{ $task['key_points'] ?? '' }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Description</label>
                                        <textarea name="tasks[{{ $i }}][description]" class="form-control" rows="3"
                                                  placeholder="Describe the task details...">{{ $task['description'] ?? '' }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-3">
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
                            <a href="{{ route('teachers.milestones.index') }}" class="btn btn-default">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                Create in All {{ $projects->count() }} Projects
                            </button>
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
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Key Points <span class="text-danger">*</span></label>
                                <textarea name="tasks[${i}][key_points]" class="form-control" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="tasks[${i}][description]" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="col-md-3">
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
                if (container.querySelectorAll('.task-row').length <= 1) {
                    alert('At least one task is required.');
                    return;
                }
                btn.closest('.task-row').remove();
                reindex();
            });
        })();
    </script>
@endsection