@extends('teachers.layouts.app')

@section('title', 'My Presentations')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>My Presentations</h2>
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
                    <div class="col-md-12">
                        <h4>Presentations for My Projects</h4>
                    </div>
                </div>

                <div class="row" style="padding: 15px;">
                    <div class="col-md-5">
                        <form action="{{ route('teachers.presentations.index') }}" method="GET">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control"
                                       placeholder="Search title, done by or project"
                                       value="{{ request('search') }}">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="submit"><i class="fa fa-search"></i></button>
                                </span>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4">
                        <form action="{{ route('teachers.presentations.index') }}" method="GET">
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
                                <th style="width: 140px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($presentations as $presentation)
                                <tr>
                                    <td>{{ $loop->iteration + ($presentations->currentPage() - 1) * $presentations->perPage() }}</td>
                                    <td>
                                        {{ $presentation->project->project_name ?? '—' }}
                                        @if($presentation->project?->group_number)
                                            <br><small class="text-muted">Group #{{ $presentation->project->group_number }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $presentation->title ?? '—' }}</td>
                                    <td>{{ $presentation->presenter->name ?? '—' }}</td>
                                    <td>{{ $presentation->date_of_presentation?->format('d M Y') ?? '—' }}</td>
                                    <td>{{ $presentation->marks !== null ? number_format($presentation->marks, 2) : '—' }}</td>
                                    <td>
                                        @if ($presentation->presentation_file)
                                            <a href="{{ asset('' . $presentation->presentation_file) }}"
                                               target="_blank" class="btn btn-xs btn-default">
                                                <i class="fa fa-file"></i> View
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
                                        <span class="label {{ $badge }}">{{ ucfirst($presentation->status ?? '—') }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('teachers.presentations.show', $presentation->id) }}"
                                           class="btn btn-sm btn-default" title="View">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ route('teachers.presentations.edit', $presentation->id) }}"
                                           class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fa fa-pencil"></i>
                                        </a>
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
