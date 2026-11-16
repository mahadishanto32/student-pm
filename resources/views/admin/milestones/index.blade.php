@extends('admin.layouts.app')

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
                        <h4>All Milestones</h4>
                    </div>
                </div>

                <div class="row" style="padding: 15px;">
                    <div class="col-md-5">
                        <form action="{{ route('admin.milestones.index') }}" method="GET">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control"
                                       placeholder="Search milestone title"
                                       value="{{ request('search') }}">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="submit"><i class="fa fa-search"></i></button>
                                </span>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <form action="{{ route('admin.milestones.index') }}" method="GET">
                            <select name="project_id" class="form-control" onchange="this.form.submit()">
                                <option value="">— All Projects —</option>
                                @foreach ($allProjects as $p)
                                    <option value="{{ $p->id }}" @selected(request('project_id') == $p->id)>
                                        Group {{ $p->group_number }} — {{ $p->project_name }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <form action="{{ route('admin.milestones.index') }}" method="GET">
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
                                <th style="width: 50px;">#</th>
                                <th>Title</th>
                                <th>Tentative Time</th>
                                <th>Tasks</th>
                                <th>Status</th>
                                <th>Marks</th>
                                <th>Done By</th>
                                <th>Supervisor Note</th>
                                <th style="width: 200px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($projects as $project)
                                @php
                                    $milestones   = $milestonesByProject[$project->id] ?? collect();
                                    $summary      = $marksSummary[$project->id] ?? null;
                                    $totalStu     = $project->teamMembers->where('role', 'student')->count();
                                    $milestoneMax = \App\Models\MarksDistribution::TOPIC_MAX['Milestones'];
                                @endphp

                                <tr class="active" style="background: #f4f6f8;">
                                    <td colspan="9">
                                        <div style="display:flex; justify-content:space-between; align-items:center;">
                                            <div>
                                                <strong>Group {{ $project->group_number ?? '—' }}</strong>
                                                — {{ $project->project_name ?? 'Untitled Project' }}
                                                @if ($project->project_topic)
                                                    <br><small class="text-muted">{{ $project->project_topic }}</small>
                                                @endif
                                            </div>
                                            <div class="text-right">
                                                @if ($summary && $summary['marked'] > 0)
                                                    <span class="label label-success">
                                                        Milestones avg: {{ $summary['avg'] }} / {{ $milestoneMax }}
                                                    </span>
                                                    <small class="text-muted">
                                                        ({{ $summary['marked'] }}/{{ $totalStu }} marked)
                                                    </small>
                                                @else
                                                    <span class="label label-default">Milestones: Not marked</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                @forelse ($milestones as $milestone)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
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
                                        <td>
                                            @if ($milestone->status === 'completed')
                                                <span class="text-success"><i class="fa fa-check"></i> Counted</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>{{ $milestone->doneBy->name ?? '—' }}</td>
                                        <td>
                                            @if ($milestone->supervisor_note)
                                                {{ \Illuminate\Support\Str::limit($milestone->supervisor_note, 60) }}
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.milestones.show', $milestone->id) }}"
                                               class="btn btn-sm btn-default" title="View">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.milestones.edit', $milestone->id) }}"
                                               class="btn btn-sm btn-primary" title="Edit">
                                                <i class="fa fa-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.milestones.destroy', $milestone->id) }}"
                                                  method="POST" style="display:inline-block"
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
                                        <td colspan="9" class="text-center text-muted">
                                            No milestones yet for this project.
                                        </td>
                                    </tr>
                                @endforelse
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">No projects found.</td>
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