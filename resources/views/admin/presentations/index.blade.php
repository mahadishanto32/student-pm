@extends('admin.layouts.app')

@section('title', 'Presentations')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Presentations</h2>
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
                        <h4>All Presentations</h4>
                    </div>
                    <div class="col-md-6 text-right">
                        {{-- Create is intentionally omitted: presentations are created by students/teachers --}}
                    </div>
                </div>

                <div class="row" style="padding: 15px;">
                    <div class="col-md-4">
                        <form action="{{ route('admin.presentations.index') }}" method="GET">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control"
                                       placeholder="Search title, presenter or project"
                                       value="{{ request('search') }}">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="submit"><i class="fa fa-search"></i></button>
                                </span>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4">
                        <form action="{{ route('admin.presentations.index') }}" method="GET">
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
                    <div class="col-md-4">
                        <form action="{{ route('admin.presentations.index') }}" method="GET">
                            <select name="status" class="form-control" onchange="this.form.submit()">
                                <option value="">— All Statuses —</option>
                                @foreach (['pending','approved','rejected','completed'] as $s)
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
                                <th>Project</th>
                                <th>Title</th>
                                <th>Done By</th>
                                <th>Date</th>
                                <th>Marks</th>
                                <th>File</th>
                                <th>Status</th>
                                <th style="width: 200px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($presentations as $presentation)
                                <tr>
                                    <td>{{ $loop->iteration + ($presentations->currentPage() - 1) * $presentations->perPage() }}</td>
                                    <td>
                                        @if ($presentation->project)
                                            Group {{ $presentation->project->group_number }}<br>
                                            <small class="text-muted">{{ $presentation->project->project_name }}</small>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $presentation->title ?? '—' }}</td>
                                    <td>{{ $presentation->presenter->name ?? '—' }}</td>
                                    <td>{{ $presentation->date_of_presentation?->format('d M Y') ?? '—' }}</td>
                                    <td>{{ $presentation->marks !== null ? number_format($presentation->marks, 2) : '—' }}</td>
                                    <td>
                                        @if ($presentation->presentation_file)
                                            <a href="{{ asset('' . $presentation->presentation_file) }}"
                                               target="_blank" class="btn btn-xs btn-default" title="View file">
                                                <i class="fa fa-file"></i>
                                            </a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $badge = match($presentation->status) {
                                                'approved'  => 'label-success',
                                                'pending'   => 'label-warning',
                                                'rejected'  => 'label-danger',
                                                'completed' => 'label-info',
                                                default     => 'label-default',
                                            };
                                        @endphp
                                        <span class="label {{ $badge }}">
                                            {{ ucfirst($presentation->status ?? '—') }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.presentations.show', $presentation->id) }}"
                                           class="btn btn-sm btn-default" title="View">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.presentations.edit', $presentation->id) }}"
                                           class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.presentations.destroy', $presentation->id) }}"
                                              method="POST" style="display:inline-block"
                                              onsubmit="return confirm('Delete this presentation?');">
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
                                    <td colspan="9" class="text-center">No presentations found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div style="padding: 0 15px 15px 15px;">
                    {{ $presentations->appends(request()->query())->links() }}
                </div>

            </div>
        </div>
    </div>
@endsection
