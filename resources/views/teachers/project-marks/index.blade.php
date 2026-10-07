@extends('teachers.layouts.app')

@section('title', 'Project Marks')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Project Marks</h2>
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
                    <div class="col-md-9">
                        <h4>Marks Given by Me</h4>
                    </div>
                    <div class="col-md-3 text-right">
                        <a href="{{ route('teachers.project-marks.create') }}" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Give Marks
                        </a>
                    </div>
                </div>

                <div class="row" style="padding: 15px;">
                    <form action="{{ route('teachers.project-marks.index') }}" method="GET">
                        <div class="col-md-5">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control"
                                       placeholder="Search student, group no or project name"
                                       value="{{ request('search') }}">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="submit"><i class="fa fa-search"></i></button>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <select name="project_id" class="form-control" onchange="this.form.submit()">
                                <option value="">— All Projects —</option>
                                @foreach ($projects as $p)
                                    <option value="{{ $p->id }}" @selected((string) request('project_id') === (string) $p->id)>
                                        Group {{ $p->group_number }} — {{ $p->project_name ?? 'Untitled' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>

                <div class="table-responsive" style="padding: 15px;">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Group No</th>
                                <th>Project Name</th>
                                <th>Student</th>
                                <th>Total Marks</th>
                                <th>Percentage</th>
                                <th>Grade</th>
                                <th>Grade Point</th>
                                <th style="width: 170px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($marks as $mark)
                                @php
                                    $info = $mark->grade_info;
                                    $badge = match(true) {
                                        str_starts_with($info['grade'], 'A') => 'label-success',
                                        str_starts_with($info['grade'], 'B') => 'label-info',
                                        str_starts_with($info['grade'], 'C') => 'label-primary',
                                        $info['grade'] === 'D'               => 'label-warning',
                                        $info['grade'] === 'F'               => 'label-danger',
                                        default                              => 'label-default',
                                    };
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration + ($marks->currentPage() - 1) * $marks->perPage() }}</td>
                                    <td>{{ $mark->project->group_number ?? '—' }}</td>
                                    <td>{{ $mark->project->project_name ?? '—' }}</td>
                                    <td>{{ $mark->student->name ?? '—' }}</td>
                                    <td>{{ rtrim(rtrim(number_format($mark->total_given, 2), '0'), '.') }} / {{ rtrim(rtrim(number_format($mark->total_out_of, 2), '0'), '.') }}</td>
                                    <td>{{ $mark->percentage }}%</td>
                                    <td><span class="label {{ $badge }}">{{ $info['grade'] }}</span></td>
                                    <td>{{ $info['point'] }}</td>
                                    <td>
                                        <a href="{{ route('teachers.project-marks.show', $mark->id) }}"
                                           class="btn btn-sm btn-default" title="View">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ route('teachers.project-marks.edit', $mark->id) }}"
                                           class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <form action="{{ route('teachers.project-marks.destroy', $mark->id) }}"
                                              method="POST" style="display:inline-block;"
                                              onsubmit="return confirm('Delete these marks? This cannot be undone.');">
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
                                    <td colspan="9" class="text-center">No marks have been given yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div style="padding: 0 15px 15px 15px;">
                    {{ $marks->appends(request()->query())->links() }}
                </div>

            </div>
        </div>
    </div>
@endsection
