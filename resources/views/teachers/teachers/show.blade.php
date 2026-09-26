@extends('teachers.layouts.app')

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
                        <a href="{{ route('teachers.presentations.index') }}" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to List
                        </a>
                        <a href="{{ route('teachers.presentations.edit', $presentation->id) }}"
                           class="btn btn-primary btn-sm">
                            <i class="fa fa-pencil"></i> Edit
                        </a>
                    </div>
                </div>

                <div class="table-responsive" style="padding: 15px;">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th style="width: 220px;">Project</th>
                                <td>
                                    {{ $presentation->project->project_name ?? '—' }}
                                    @if($presentation->project?->group_number)
                                        <small class="text-muted">(Group #{{ $presentation->project->group_number }})</small>
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
                                <th>Marks</th>
                                <td>{{ $presentation->marks !== null ? number_format($presentation->marks, 2) : '—' }}</td>
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
                                        <a href="{{ asset('uploads/presentations/' . $presentation->presentation_file) }}"
                                           target="_blank" class="btn btn-sm btn-default">
                                            <i class="fa fa-file"></i> Download / View
                                        </a>
                                    @else
                                        <span class="text-muted">No file uploaded</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
@endsection
