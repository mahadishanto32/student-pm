@extends('admin.layouts.app')

@section('title', 'Video Resume Details')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Video Resume Details</h2>
            </div>
        </div>
    </div>

    <div class="row column1">
        <div class="col-md-12">
            <div class="full white_shadow_bg margin_bottom_30">

                <div class="row" style="padding: 15px 15px 0 15px;">
                    <div class="col-md-6">
                        <h4>{{ $videoResume->title ?? 'Untitled Video Resume' }}</h4>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="{{ route('admin.video-resumes.edit', $videoResume->id) }}" class="btn btn-primary">
                            <i class="fa fa-pencil"></i> Edit
                        </a>
                        <a href="{{ route('admin.video-resumes.index') }}" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                <div style="padding: 15px;">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 200px;">Project</th>
                            <td>{{ $videoResume->project->project_name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Group Number</th>
                            <td>{{ $videoResume->project->group_number ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Assigned Teacher</th>
                            <td>{{ $videoResume->project->teacher->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Team Members</th>
                            <td>
                                @forelse ($videoResume->project?->teamMembers ?? [] as $member)
                                    <span class="label label-default">{{ $member->name }}</span>
                                @empty
                                    —
                                @endforelse
                            </td>
                        </tr>
                        <tr>
                            <th>Title</th>
                            <td>{{ $videoResume->title ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Uploaded At</th>
                            <td>{{ $videoResume->created_at?->format('d M Y, h:i A') ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Last Updated</th>
                            <td>{{ $videoResume->updated_at?->format('d M Y, h:i A') ?? '—' }}</td>
                        </tr>
                    </table>

                    @if ($videoResume->media_file)
                        <video controls preload="metadata" style="max-width: 100%; width: 720px;">
                            <source src="{{ asset($videoResume->media_file) }}">
                            Your browser does not support the video tag.
                        </video>
                        <p style="margin-top: 10px;">
                            <a href="{{ asset($videoResume->media_file) }}" target="_blank" class="btn btn-default btn-sm">
                                <i class="fa fa-external-link"></i> Open in new tab
                            </a>
                        </p>
                    @else
                        <p class="text-muted">No video uploaded.</p>
                    @endif
                </div>

            </div>
        </div>
    </div>
@endsection
