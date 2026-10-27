@extends('teachers.layouts.app')

@section('title', 'Project Book Details')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Project Book Details</h2>
            </div>
        </div>
    </div>

    <div class="row column1">
        <div class="col-md-12">
            <div class="full white_shadow_bg margin_bottom_30">

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <div class="row" style="padding: 15px;">
                    <div class="col-md-8">
                        <h4>
                            {{ $projectBook->project->project_name ?? 'Untitled Project' }}
                            <small class="text-muted">
                                (Group {{ $projectBook->project->group_number ?? '—' }})
                            </small>
                        </h4>
                    </div>
                    <div class="col-md-4 text-right">
                        @php
                            $badge = match($projectBook->status) {
                                'approved'  => 'label-success',
                                'pending'   => 'label-warning',
                                'working'   => 'label-primary',
                                'completed' => 'label-info',
                                'rejected'  => 'label-danger',
                                'cancelled' => 'label-default',
                                default     => 'label-default',
                            };
                        @endphp
                        <span class="label {{ $badge }}" style="font-size: 13px;">
                            {{ ucfirst($projectBook->status) }}
                        </span>
                    </div>
                </div>

                <div class="table-responsive" style="padding: 15px;">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th style="width: 220px;">Project</th>
                                <td>{{ $projectBook->project->project_name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Topic</th>
                                <td>{{ $projectBook->project->project_topic ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Group No</th>
                                <td>{{ $projectBook->project->group_number ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Team Members</th>
                                <td>
                                    @forelse ($projectBook->project->teamMembers ?? [] as $member)
                                        <span class="label label-info">{{ $member->name }}</span>
                                    @empty
                                        <span class="text-muted">—</span>
                                    @endforelse
                                </td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td><span class="label {{ $badge }}">{{ ucfirst($projectBook->status) }}</span></td>
                            </tr>
                            <tr>
                                <th>Created At</th>
                                <td>{{ $projectBook->created_at?->format('d M Y, H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Last Updated</th>
                                <td>{{ $projectBook->updated_at?->format('d M Y, H:i') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                @if ($projectBook->status === 'completed')
                    @php $perTopic = \App\Models\ProjectMark::MARKS_PER_TOPIC; @endphp

                    <div class="row" style="padding: 0 15px;">
                        <div class="col-md-12">
                            <h4>Student Marks (out of {{ $perTopic }})</h4>
                        </div>
                    </div>

                    <div class="table-responsive" style="padding: 15px;">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">#</th>
                                    <th>Student</th>
                                    <th style="width: 150px;">Marks</th>
                                    <th style="width: 120px;">Percentage</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($students as $student)
                                    @php
                                        $pm    = $marks[$student->id] ?? null;
                                        $dist  = $pm?->distributions->first();
                                        $given = $dist?->given_marks;
                                        $outOf = $dist?->out_of ?? $perTopic;
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $student->name }}</td>
                                        <td>
                                            @if ($given !== null)
                                                <strong>{{ rtrim(rtrim(number_format((float) $given, 2), '0'), '.') }}</strong>
                                                / {{ rtrim(rtrim(number_format((float) $outOf, 2), '0'), '.') }}
                                            @else
                                                <span class="label label-default">Not marked</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($given !== null && $outOf > 0)
                                                {{ number_format(($given / $outOf) * 100, 1) }}%
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>{{ $pm?->remarks ?: '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No students found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif

                <div class="row" style="padding: 0 15px;">
                    <div class="col-md-12">
                        <h4>Chapters</h4>
                    </div>
                </div>

                <div class="table-responsive" style="padding: 15px;">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 80px;">No</th>
                                <th>Title</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($projectBook->chapters as $chapter)
                                <tr>
                                    <td>{{ $chapter->chapter_no }}</td>
                                    <td>{{ $chapter->chapter_title }}</td>
                                    <td>{{ $chapter->chapter_description ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No chapters added yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="row" style="padding: 0 15px 15px 15px;">
                    <div class="col-md-12">
                        <a href="{{ route('teachers.project-books.edit', $projectBook->id) }}"
                           class="btn btn-primary">
                            <i class="fa fa-pencil"></i> Edit
                        </a>
                        <form action="{{ route('teachers.project-books.destroy', $projectBook->id) }}"
                              method="POST" style="display:inline-block"
                              onsubmit="return confirm('Delete this project book?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger">
                                <i class="fa fa-trash"></i> Delete
                            </button>
                        </form>
                        <a href="{{ route('teachers.project-books.index') }}" class="btn btn-default">
                            Back to List
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection