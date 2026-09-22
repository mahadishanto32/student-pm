@extends('layouts.app')

@section('title', 'Project Books')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Project Books</h2>
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
                        <h4>Books of My Projects</h4>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="{{ route('student.project-books.create') }}" class="btn btn-success">
                            <i class="fa fa-plus"></i> New Project Book
                        </a>
                    </div>
                </div>

                <div class="row" style="padding: 15px;">
                    <div class="col-md-5">
                        <form action="{{ route('student.project-books.index') }}" method="GET">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control"
                                       placeholder="Search project name, topic or group no"
                                       value="{{ request('search') }}">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="submit"><i class="fa fa-search"></i></button>
                                </span>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4">
                        <form action="{{ route('student.project-books.index') }}" method="GET">
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
                                <th>Chapters</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th style="width: 180px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($projectBooks as $book)
                                <tr>
                                    <td>{{ $loop->iteration + ($projectBooks->currentPage() - 1) * $projectBooks->perPage() }}</td>
                                    <td>{{ $book->project->group_number ?? '—' }}</td>
                                    <td>{{ $book->project->project_name ?? '—' }}</td>
                                    <td>{{ $book->project->project_topic ?? '—' }}</td>
                                    <td>{{ $book->chapters()->count() }}</td>
                                    <td>
                                        @php
                                            $badge = match($book->status) {
                                                'approved'  => 'label-success',
                                                'pending'   => 'label-warning',
                                                'working'   => 'label-primary',
                                                'completed' => 'label-info',
                                                'rejected'  => 'label-danger',
                                                'cancelled' => 'label-default',
                                                default     => 'label-default',
                                            };
                                        @endphp
                                        <span class="label {{ $badge }}">{{ ucfirst($book->status) }}</span>
                                    </td>
                                    <td>{{ $book->created_at?->format('d M Y') }}</td>
                                    <td>
                                        <a href="{{ route('student.project-books.show', $book->id) }}"
                                           class="btn btn-sm btn-default" title="View">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ route('student.project-books.edit', $book->id) }}"
                                           class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <form action="{{ route('student.project-books.destroy', $book->id) }}"
                                              method="POST" style="display:inline-block"
                                              onsubmit="return confirm('Delete this project book?');">
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
                                    <td colspan="8" class="text-center">No project books found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div style="padding: 0 15px 15px 15px;">
                    {{ $projectBooks->appends(request()->query())->links() }}
                </div>

            </div>
        </div>
    </div>
@endsection