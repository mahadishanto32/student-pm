@extends('admin.layouts.app')

@section('title', 'Presentation Details')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Presentation Details</h2>
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

                <div class="row" style="padding: 15px;">
                    <div class="col-md-12">
                        <a href="{{ route('admin.presentations.index') }}" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to List
                        </a>
                        <a href="{{ route('admin.presentations.edit', $presentation->id) }}"
                           class="btn btn-primary btn-sm">
                            <i class="fa fa-pencil"></i> Edit
                        </a>
                        <form action="{{ route('admin.presentations.destroy', $presentation->id) }}"
                              method="POST" style="display:inline-block"
                              onsubmit="return confirm('Delete this presentation?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm">
                                <i class="fa fa-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>

                <div class="table-responsive" style="padding: 15px;">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th style="width: 220px;">Project</th>
                                <td>
                                    @if ($presentation->project)
                                        Group {{ $presentation->project->group_number }} —
                                        {{ $presentation->project->project_name }}
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Title</th>
                                <td>{{ $presentation->title ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Done By</th>
                                <td>{{ $presentation->presenter->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Key Points</th>
                                <td>{!! nl2br(e($presentation->key_points ?? '—')) !!}</td>
                            </tr>
                            <tr>
                                <th>Date of Presentation</th>
                                <td>{{ $presentation->date_of_presentation?->format('d M Y') ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Supervisor Feedback</th>
                                <td>{!! nl2br(e($presentation->supervisor_feedback ?? '—')) !!}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
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
                            </tr>
                            <tr>
                                <th>Presentation File</th>
                                <td>
                                    @if ($presentation->presentation_file)
                                        <a href="{{ asset('' . $presentation->presentation_file) }}"
                                           target="_blank" class="btn btn-sm btn-default">
                                            <i class="fa fa-file"></i> Download / View
                                        </a>
                                    @else
                                        <span class="text-muted">No file uploaded</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Created</th>
                                <td>{{ $presentation->created_at?->format('d M Y, h:i A') ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Last Updated</th>
                                <td>{{ $presentation->updated_at?->format('d M Y, h:i A') ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                @if ($presentation->status === 'completed')
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
                                    <th>Marked By</th>
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
                                        <td>{{ $given !== null ? ($pm?->supervisor?->name ?? '—') : '—' }}</td>
                                        <td>{{ $pm?->remarks ?: '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No students found in this project's team.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif

            </div>
        </div>
    </div>
@endsection
