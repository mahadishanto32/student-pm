@extends('layouts.app')

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
                        <h4>My Presentations</h4>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="{{ route('student.presentations.create') }}" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Add Presentation
                        </a>
                    </div>
                </div>

                <div class="row" style="padding: 15px;">
                    <div class="col-md-5">
                        <form action="{{ route('student.presentations.index') }}" method="GET">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control"
                                       placeholder="Search title, key points or project"
                                       value="{{ request('search') }}">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="submit"><i class="fa fa-search"></i></button>
                                </span>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-3">
                        <form action="{{ route('student.presentations.index') }}" method="GET">
                            <select name="status" class="form-control" onchange="this.form.submit()">
                                <option value="">— All Statuses —</option>
                                @foreach ($statuses as $s)
                                    <option value="{{ $s }}" @selected(request('status') === $s)>
                                        {{ ucfirst($s) }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    <div class="col-md-4">
                        <form action="{{ route('student.presentations.index') }}" method="GET">
                            <select name="project_id" class="form-control" onchange="this.form.submit()">
                                <option value="">— All Projects —</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}" @selected((int) request('project_id') === $project->id)>
                                        {{ $project->project_name }}
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
                                <th>Title</th>
                                <th>Project</th>
                                <th>Date</th>
                                <th>Marks</th>
                                <th>Status</th>
                                <th>File</th>
                                <th style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($presentations as $presentation)
                                <tr>
                                    <td>{{ $loop->iteration + ($presentations->currentPage() - 1) * $presentations->perPage() }}</td>
                                    <td>{{ $presentation->title }}</td>
                                    <td>
                                        @if ($presentation->project)
                                            {{ $presentation->project->project_name }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $presentation->date_of_presentation?->format('d M Y') ?? '—' }}</td>
                                    <td>{{ $presentation->marks !== null ? $presentation->marks : '—' }}</td>
                                    <td>
                                        @php
                                            $statusBadge = match($presentation->status) {
                                                'approved'  => 'label-success',
                                                'completed' => 'label-info',
                                                'rejected'  => 'label-danger',
                                                'pending'   => 'label-warning',
                                                default     => 'label-default',
                                            };
                                        @endphp
                                        <span class="label {{ $statusBadge }}">{{ ucfirst($presentation->status) }}</span>
                                    </td>
                                    <td>
                                        @if ($presentation->presentation_file)
                                            <a href="{{ asset($presentation->presentation_file) }}"
                                               target="_blank" class="btn btn-xs btn-default">
                                                <i class="fa fa-download"></i> View
                                            </a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('student.presentations.show', $presentation->id) }}"
                                           class="btn btn-sm btn-default" title="View">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ route('student.presentations.edit', $presentation->id) }}"
                                           class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <form action="{{ route('student.presentations.destroy', $presentation->id) }}"
                                              method="POST" style="display:inline-block;"
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
                                    <td colspan="8" class="text-center">No presentations found.</td>
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