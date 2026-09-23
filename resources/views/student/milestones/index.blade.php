@extends('layouts.app')

@section('title', 'Project Milestones')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Project Milestones</h2>
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
                        <h4>Milestones</h4>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="{{ route('student.milestones.create') }}" class="btn btn-success">
                            <i class="fa fa-plus"></i> New Milestone
                        </a>
                    </div>
                </div>

                <div class="row" style="padding: 15px;">
                    <div class="col-md-5">
                        <form action="{{ route('student.milestones.index') }}" method="GET">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control"
                                       placeholder="Search title"
                                       value="{{ request('search') }}">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="submit"><i class="fa fa-search"></i></button>
                                </span>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <form action="{{ route('student.milestones.index') }}" method="GET">
                            <select name="project_id" class="form-control" onchange="this.form.submit()">
                                <option value="">— All Projects —</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}" @selected(request('project_id') == $project->id)>
                                        Group {{ $project->group_number }} — {{ $project->project_name }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <form action="{{ route('student.milestones.index') }}" method="GET">
                            <select name="status" class="form-control" onchange="this.form.submit()">
                                <option value="">— All Statuses —</option>
                                @foreach (['pending','need_correction','completed','rejected'] as $s)
                                    <option value="{{ $s }}" @selected(request('status') === $s)>
                                        {{ ucwords(str_replace('_', ' ', $s)) }}
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
                                <th>Project</th>
                                <th>Title</th>
                                <th>Tentative Time</th>
                                <th>Tasks</th>
                                <th>Status</th>
                                <th>Done By</th>
                                <th style="width: 170px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($milestones as $milestone)
                                <tr>
                                    <td>{{ $loop->iteration + ($milestones->currentPage() - 1) * $milestones->perPage() }}</td>
                                    <td>
                                        @if ($milestone->project)
                                            Group {{ $milestone->project->group_number }}<br>
                                            <small class="text-muted">{{ $milestone->project->project_name }}</small>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $milestone->title }}</td>
                                    <td>{{ $milestone->tentative_time?->format('d M Y, h:i A') ?? '—' }}</td>
                                    <td><span class="label label-default">{{ $milestone->tasks->count() }}</span></td>
                                    <td>
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
                                    </td>
                                    <td>{{ $milestone->doneBy->name ?? '—' }}</td>
                                    <td>
                                        <a href="{{ route('student.milestones.show', $milestone->id) }}"
                                           class="btn btn-sm btn-default" title="View">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        @if ($milestone->status !== 'completed')
                                            <a href="{{ route('student.milestones.edit', $milestone->id) }}"
                                               class="btn btn-sm btn-primary" title="Edit">
                                                <i class="fa fa-pencil"></i>
                                            </a>
                                        @endif
                                        <form action="{{ route('student.milestones.destroy', $milestone->id) }}"
                                              method="POST" style="display:inline-block;"
                                              onsubmit="return confirm('Delete this milestone and all its tasks?');">
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
                                    <td colspan="8" class="text-center">No milestones found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div style="padding: 0 15px 15px 15px;">
                    {{ $milestones->appends(request()->query())->links() }}
                </div>

            </div>
        </div>
    </div>
@endsection