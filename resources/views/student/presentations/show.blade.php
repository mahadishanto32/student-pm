@extends('layouts.app')

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

                <div class="row" style="padding: 15px;">
                    <div class="col-md-12">

                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 220px;">Title</th>
                                <td>{{ $presentation->title }}</td>
                            </tr>
                            <tr>
                                <th>Project</th>
                                <td>{{ $presentation->project->project_name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Presented By</th>
                                <td>{{ $presentation->presenter->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Date of Presentation</th>
                                <td>{{ $presentation->date_of_presentation?->format('d M Y') ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Key Points</th>
                                <td>{!! nl2br(e($presentation->key_points ?? '—')) !!}</td>
                            </tr>
                            <tr>
                                <th>Supervisor Feedback</th>
                                <td>{!! nl2br(e($presentation->supervisor_feedback ?? '—')) !!}</td>
                            </tr>
                            <tr>
                                <th>Marks</th>
                                <td>{{ $presentation->marks !== null ? $presentation->marks : '—' }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>{{ ucfirst($presentation->status) }}</td>
                            </tr>
                            <tr>
                                <th>File</th>
                                <td>
                                    @if ($presentation->presentation_file)
                                        <a href="{{ asset($presentation->presentation_file) }}"
                                           target="_blank" class="btn btn-sm btn-default">
                                            <i class="fa fa-download"></i> Download
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        </table>

                        <a href="{{ route('student.presentations.edit', $presentation->id) }}"
                           class="btn btn-primary">
                            <i class="fa fa-pencil"></i> Edit
                        </a>
                        <a href="{{ route('student.presentations.index') }}" class="btn btn-default">
                            Back
                        </a>

                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection