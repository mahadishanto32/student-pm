@extends('admin.layouts.app')

@section('title', 'Manage Projects')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Manage Projects</h2>
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
                        <h4>All Projects</h4>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="{{ route('admin.projects.create') }}" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Add New Project
                        </a>
                    </div>
                </div>

                <div class="row" style="padding: 15px;">
                    <div class="col-md-5">
                        <form action="{{ route('admin.projects.index') }}" method="GET">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control"
                                       placeholder="Search group no, name or topic"
                                       value="{{ request('search') }}">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="submit"><i class="fa fa-search"></i></button>
                                </span>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4">
                        <form action="{{ route('admin.projects.index') }}" method="GET">
                            <select name="status" class="form-control" onchange="this.form.submit()">
                                <option value="">— All Statuses —</option>
                                @foreach (['approved','pending','working','completed','rejected','cancelled'] as $s)
                                    <option value="{{ $s }}" @selected(request('status') === $s)>
                                        {{ ucfirst($s) }}
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
                                <th>Group No</th>
                                <th>Project Name</th>
                                <th>Topic</th>
                                <th>Teacher</th>
                                <th>Team Members</th>
                                <th>Start</th>
                                <th>End</th>
                                <th>Status</th>
                                <th style="width: 200px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($projects as $project)
                                <tr>
                                    <td>{{ $loop->iteration + ($projects->currentPage() - 1) * $projects->perPage() }}</td>
                                    <td>{{ $project->group_number }}</td>
                                    <td>{{ $project->project_name ?? '—' }}</td>
                                    <td>{{ $project->project_topic ?? '—' }}</td>
                                    <td>{{ $project->teacher->name ?? '—' }}</td>
                                    <td>
                                        @forelse ($project->teamMembers as $member)
                                            <span class="label label-info">{{ $member->name }}</span>
                                        @empty
                                            <span class="text-muted">—</span>
                                        @endforelse
                                    </td>
                                    <td>{{ $project->start_date?->format('d M Y') }}</td>
                                    <td>{{ $project->tentative_end_date?->format('d M Y') ?? '—' }}</td>
                                    <td>
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
                                        <span class="label {{ $badge }}">{{ ucfirst($project->status) }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.projects.show', $project->id) }}" class="btn btn-sm btn-default" title="View">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.projects.edit', $project->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.projects.destroy', $project->id) }}" method="POST"
                                              style="display:inline-block;"
                                              onsubmit="return confirm('Are you sure you want to delete this project?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center">No projects found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div style="padding: 0 15px 15px 15px;">
                    {{ $projects->appends(request()->query())->links() }}
                </div>

            </div>
        </div>
    </div>
@endsection