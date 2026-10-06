@extends('layouts.app')

@section('title', 'Video Resumes')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Video Resumes</h2>
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
                        <h4>My Video Resumes</h4>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="{{ route('student.video-resumes.create') }}" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Add Video Resume
                        </a>
                    </div>
                </div>

                <div class="row" style="padding: 15px;">
                    <div class="col-md-7">
                        <form action="{{ route('student.video-resumes.index') }}" method="GET">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control"
                                       placeholder="Search title or project"
                                       value="{{ request('search') }}">
                                <span class="input-group-btn">
                                    <button class="btn btn-default" type="submit"><i class="fa fa-search"></i></button>
                                </span>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-5">
                        <form action="{{ route('student.video-resumes.index') }}" method="GET">
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
                                <th>Uploaded</th>
                                <th>Video</th>
                                <th style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($videoResumes as $videoResume)
                                <tr>
                                    <td>{{ $loop->iteration + ($videoResumes->currentPage() - 1) * $videoResumes->perPage() }}</td>
                                    <td>{{ $videoResume->title ?? '—' }}</td>
                                    <td>
                                        @if ($videoResume->project)
                                            {{ $videoResume->project->project_name }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $videoResume->created_at?->format('d M Y') ?? '—' }}</td>
                                    <td>
                                        @if ($videoResume->media_file)
                                            <a href="{{ asset($videoResume->media_file) }}"
                                               target="_blank" class="btn btn-xs btn-default">
                                                <i class="fa fa-play"></i> Watch
                                            </a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('student.video-resumes.show', $videoResume->id) }}"
                                           class="btn btn-sm btn-default" title="View">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ route('student.video-resumes.edit', $videoResume->id) }}"
                                           class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                        <form action="{{ route('student.video-resumes.destroy', $videoResume->id) }}"
                                              method="POST" style="display:inline-block;"
                                              onsubmit="return confirm('Delete this video resume?');">
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
                                    <td colspan="6" class="text-center">No video resumes found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div style="padding: 0 15px 15px 15px;">
                    {{ $videoResumes->appends(request()->query())->links() }}
                </div>

            </div>
        </div>
    </div>
@endsection
