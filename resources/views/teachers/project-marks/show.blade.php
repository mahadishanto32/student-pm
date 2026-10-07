@extends('teachers.layouts.app')

@section('title', 'Marks Details')

@section('content')
    @php
        $info = $mark->grade_info;
        $fmt  = fn ($n) => rtrim(rtrim(number_format((float) $n, 2), '0'), '.');
    @endphp

    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Marks Details</h2>
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
                    <div class="col-md-8">
                        <h4>{{ $mark->student->name ?? '—' }}</h4>
                    </div>
                    <div class="col-md-4 text-right">
                        <a href="{{ route('teachers.project-marks.index') }}" class="btn btn-default">
                            <i class="fa fa-arrow-left"></i> Back
                        </a>
                        <a href="{{ route('teachers.project-marks.edit', $mark->id) }}" class="btn btn-primary">
                            <i class="fa fa-pencil"></i> Edit
                        </a>
                        <form action="{{ route('teachers.project-marks.destroy', $mark->id) }}" method="POST"
                              style="display:inline-block;"
                              onsubmit="return confirm('Delete these marks? This cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> Delete</button>
                        </form>
                    </div>
                </div>

                {{-- Basic information --}}
                <div style="padding: 15px;">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th style="width:200px;">Group No</th>
                                <td>{{ $mark->project->group_number ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Project Name</th>
                                <td>{{ $mark->project->project_name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Project Topic</th>
                                <td>{{ $mark->project->project_topic ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Student</th>
                                <td>{{ $mark->student->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Supervisor</th>
                                <td>{{ $mark->supervisor->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Remarks</th>
                                <td>{!! nl2br(e($mark->remarks ?? '—')) !!}</td>
                            </tr>
                            <tr>
                                <th>Last Updated</th>
                                <td>{{ $mark->updated_at?->format('d M Y, h:i A') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Marks distribution --}}
                <div style="padding: 0 15px;">
                    <h4>Marks Distribution</h4>
                </div>
                <div class="table-responsive" style="padding: 15px;">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width:50px;">#</th>
                                <th>Topic</th>
                                <th>Given Marks</th>
                                <th>Out Of</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($topics as $i => $topic)
                                @php $d = $distributions[$topic] ?? null; @endphp
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $topic }}</td>
                                    <td>{{ $d ? $fmt($d->given_marks) : '—' }}</td>
                                    <td>{{ $d ? $fmt($d->out_of) : '—' }}</td>
                                    <td>{{ $d && $d->out_of > 0 ? round(($d->given_marks / $d->out_of) * 100, 2) . '%' : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2" class="text-right">Total</th>
                                <th>{{ $fmt($mark->total_given) }}</th>
                                <th>{{ $fmt($mark->total_out_of) }}</th>
                                <th>{{ $mark->percentage }}%</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Result summary --}}
                <div class="row" style="padding: 0 15px 15px 15px;">
                    <div class="col-md-4">
                        <div class="alert alert-info text-center" style="margin-bottom:0;">
                            <strong>Percentage</strong><br>
                            <span style="font-size:24px;">{{ $mark->percentage }}%</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="alert alert-success text-center" style="margin-bottom:0;">
                            <strong>Grade</strong><br>
                            <span style="font-size:24px;">{{ $info['grade'] }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="alert alert-warning text-center" style="margin-bottom:0;">
                            <strong>Grade Point</strong><br>
                            <span style="font-size:24px;">{{ $info['point'] }}</span>
                        </div>
                    </div>
                </div>

                {{-- Grading system --}}
                <div style="padding: 0 15px;">
                    <h4>Grading System</h4>
                </div>
                <div class="table-responsive" style="padding: 15px;">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Marks</th>
                                <th>Grade</th>
                                <th>Grade Point</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($gradingScale as $row)
                                <tr class="{{ $info['grade'] === $row['grade'] ? 'success' : '' }}">
                                    <td>{{ $row['range'] }}</td>
                                    <td><strong>{{ $row['grade'] }}</strong></td>
                                    <td>{{ $row['point'] }}</td>
                                </tr>
                            @endforeach
                            <tr class="{{ $info['grade'] === 'I' ? 'success' : '' }}">
                                <td>Incomplete</td>
                                <td><strong>I</strong></td>
                                <td>-</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
@endsection
