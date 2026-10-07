@extends('layouts.app')

@section('title', 'Project Marks Details')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Project Marks Details</h2>
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

                {{-- Project Information --}}
                <div class="row" style="padding: 15px 15px 0 15px;">
                    <div class="col-md-8">
                        <h4>Project Information</h4>
                    </div>
                    <div class="col-md-4 text-right">
                        <a href="{{ route('student.project-marks.index') }}" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Back to My Marks
                        </a>
                    </div>
                </div>

                <div class="row" style="padding: 15px;">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 40%;">Group Number</th>
                                <td>{{ $projectMark->project->group_number ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Project Name</th>
                                <td>{{ $projectMark->project->project_name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Project Topic</th>
                                <td>{{ $projectMark->project->project_topic ?? '—' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 40%;">Supervisor</th>
                                <td>{{ $projectMark->supervisor->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Student</th>
                                <td>{{ $projectMark->student->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Remarks</th>
                                <td>{{ $projectMark->remarks ?? '—' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- Marks Distribution --}}
                <div class="row" style="padding: 0 15px;">
                    <div class="col-md-12">
                        <h4>Marks Distribution</h4>
                    </div>
                </div>

                <div class="table-responsive" style="padding: 15px;">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Topic</th>
                                <th>Given Marks</th>
                                <th>Out Of</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $distributions = $projectMark->distributions->keyBy('topic');
                            @endphp
                            @foreach ($topics as $index => $topic)
                                @php $dist = $distributions->get($topic); @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $topic }}</td>
                                    <td>{{ $dist ? number_format($dist->given_marks, 2) : '—' }}</td>
                                    <td>{{ $dist ? number_format($dist->out_of, 2) : '—' }}</td>
                                    <td>
                                        @if ($dist && $dist->out_of > 0)
                                            {{ number_format(($dist->given_marks / $dist->out_of) * 100, 2) }}%
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="active">
                                <th colspan="2" class="text-right">Total</th>
                                <th>{{ number_format($projectMark->total_given, 2) }}</th>
                                <th>{{ number_format($projectMark->total_out_of, 2) }}</th>
                                <th>
                                    @if ($projectMark->is_complete)
                                        {{ number_format($projectMark->percentage, 2) }}%
                                    @else
                                        Incomplete
                                    @endif
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Grading Result --}}
                <div class="row" style="padding: 0 15px;">
                    <div class="col-md-12">
                        <h4>Grading Result</h4>
                    </div>
                </div>

                @php
                    $gradeInfo = $projectMark->grade_info;
                    $gradeBadge = match($gradeInfo['grade']) {
                        'A+', 'A', 'A-' => 'label-success',
                        'B+', 'B', 'B-' => 'label-primary',
                        'C+', 'C', 'D'  => 'label-warning',
                        'F'             => 'label-danger',
                        default         => 'label-default',
                    };
                @endphp

                <div class="row" style="padding: 15px;">
                    <div class="col-md-4">
                        <div class="full" style="border: 1px solid #ddd; padding: 20px; text-align: center; border-radius: 5px;">
                            <h5>Total Percentage</h5>
                            <h2 style="margin: 10px 0;">
                                @if ($projectMark->is_complete)
                                    {{ number_format($projectMark->percentage, 2) }}%
                                @else
                                    —
                                @endif
                            </h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="full" style="border: 1px solid #ddd; padding: 20px; text-align: center; border-radius: 5px;">
                            <h5>Grade</h5>
                            <h2 style="margin: 10px 0;">
                                <span class="label {{ $gradeBadge }}" style="font-size: 24px; padding: 8px 16px;">
                                    {{ $gradeInfo['grade'] }}
                                </span>
                            </h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="full" style="border: 1px solid #ddd; padding: 20px; text-align: center; border-radius: 5px;">
                            <h5>Grade Point</h5>
                            <h2 style="margin: 10px 0;">{{ $gradeInfo['point'] }}</h2>
                        </div>
                    </div>
                </div>

                @if ($gradeInfo['grade'] === 'I')
                    <div class="row" style="padding: 0 15px;">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i>
                                Your grade is <strong>Incomplete (I)</strong> because not all topics have been marked yet.
                                Please wait for your supervisor to complete the evaluation.
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Grading Scale Reference --}}
                <div class="row" style="padding: 0 15px;">
                    <div class="col-md-12">
                        <h4>Grading Scale Reference</h4>
                    </div>
                </div>

                <div class="table-responsive" style="padding: 15px;">
                    <table class="table table-bordered table-condensed">
                        <thead>
                            <tr class="active">
                                <th>Marks</th>
                                <th>Grade</th>
                                <th>Grade Point</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($gradingScale as $row)
                                <tr @if ($gradeInfo['grade'] === $row['grade'] && $gradeInfo['grade'] !== 'I') class="success" @endif>
                                    <td>{{ $row['range'] }}</td>
                                    <td>
                                        <span class="label {{ $gradeInfo['grade'] === $row['grade'] && $gradeInfo['grade'] !== 'I' ? 'label-success' : 'label-default' }}">
                                            {{ $row['grade'] }}
                                        </span>
                                    </td>
                                    <td>{{ $row['point'] }}</td>
                                </tr>
                            @endforeach
                            <tr @if ($gradeInfo['grade'] === 'I') class="info" @endif>
                                <td>Incomplete</td>
                                <td><span class="label label-default">I</span></td>
                                <td>—</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
@endsection
