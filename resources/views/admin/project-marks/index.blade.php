@extends('admin.layouts.app')

@section('title', 'Manage Project Marks')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Manage Project Marks</h2>
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
                        <h4>All Project Marks</h4>
                    </div>
                </div>

                <div class="row" style="padding: 15px;">
                    <div class="col-md-5">
                        <form action="{{ route('admin.project-marks.index') }}" method="GET">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control"
                                       placeholder="Search group no, project, student or supervisor"
                                       value="{{ request('search') }}">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="submit"><i class="fa fa-search"></i></button>
                                </span>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <form action="{{ route('admin.project-marks.index') }}" method="GET">
                            <select name="project_id" class="form-control" onchange="this.form.submit()">
                                <option value="">— All Projects —</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}" @selected(request('project_id') == $project->id)>
                                        {{ $project->group_number }} — {{ $project->project_name ?? 'Untitled' }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <form action="{{ route('admin.project-marks.index') }}" method="GET">
                            <select name="grade" class="form-control" onchange="this.form.submit()">
                                <option value="">— All Grades —</option>
                                @foreach (['A+','A','A-','B+','B','B-','C+','C','D','F','I'] as $g)
                                    <option value="{{ $g }}" @selected(request('grade') === $g)>
                                        {{ $g }}
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
                                <th>Student</th>
                                <th>Supervisor</th>
                                <th>Total Marks</th>
                                <th>Percentage</th>
                                <th>Grade</th>
                                <th>Grade Point</th>
                                <th style="width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($projectMarks as $mark)
                                @php
                                    $gradeInfo = $mark->grade_info;
                                    $gradeBadge = match($gradeInfo['grade']) {
                                        'A+', 'A', 'A-' => 'label-success',
                                        'B+', 'B', 'B-' => 'label-primary',
                                        'C+', 'C', 'D'  => 'label-warning',
                                        'F'             => 'label-danger',
                                        default         => 'label-default',
                                    };
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration + ($projectMarks->currentPage() - 1) * $projectMarks->perPage() }}</td>
                                    <td>{{ $mark->project->group_number ?? '—' }}</td>
                                    <td>{{ $mark->project->project_name ?? '—' }}</td>
                                    <td>{{ $mark->student->name ?? '—' }}</td>
                                    <td>{{ $mark->supervisor->name ?? '—' }}</td>
                                    <td>
                                        @if ($mark->is_complete)
                                            {{ number_format($mark->total_given, 2) }} / {{ number_format($mark->total_out_of, 2) }}
                                        @else
                                            <span class="text-muted">Incomplete</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($mark->is_complete)
                                            {{ number_format($mark->percentage, 2) }}%
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="label {{ $gradeBadge }}">{{ $gradeInfo['grade'] }}</span>
                                    </td>
                                    <td>{{ $gradeInfo['point'] }}</td>
                                    <td>
                                        <a href="{{ route('admin.project-marks.show', $mark->id) }}"
                                           class="btn btn-sm btn-default" title="View Details">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center">No project marks found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div style="padding: 0 15px 15px 15px;">
                    {{ $projectMarks->appends(request()->query())->links() }}
                </div>

            </div>
        </div>
    </div>
@endsection
