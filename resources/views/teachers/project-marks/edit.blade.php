@extends('teachers.layouts.app')

@section('title', 'Edit Project Marks')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Edit Project Marks</h2>
            </div>
        </div>
    </div>

    <div class="row column1">
        <div class="col-md-12">
            <div class="full white_shadow_bg margin_bottom_30">

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul style="margin-bottom: 0;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row" style="padding: 15px 15px 0 15px;">
                    <div class="col-md-12">
                        <h4>
                            {{ $project->project_name ?? 'Untitled Project' }}
                            <small class="text-muted">(Group {{ $project->group_number ?? '—' }})</small>
                        </h4>
                        <p class="text-muted">
                            Each topic is out of {{ $perTopic }}. Leave a field blank to clear that mark.
                        </p>
                    </div>
                </div>

                <form action="{{ route('teachers.project-marks.update', $project->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    @php
                        $topicMax = \App\Models\MarksDistribution::TOPIC_MAX;
                        $perTopic = $topicMax; // keys: Milestones, Project Book, Presentation
                    @endphp

                    <div class="table-responsive" style="padding: 15px;">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Student</th>
                                    <th style="width: 130px;">Milestones<br>
                                        <small class="text-muted">0 - {{ $perTopic['Milestones'] }} (auto from milestones)</small>
                                    </th>
                                    <th style="width: 130px;">Project Book<br>
                                        <small class="text-muted">0 - {{ $perTopic['Project Book'] }} (read-only)</small>
                                    </th>
                                    <th style="width: 130px;">Presentation<br>
                                        <small class="text-muted">0 - {{ $perTopic['Presentation'] }} (read-only)</small>
                                    </th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rows as $row)
                                    @php $sid = $row['student']->id; @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $row['student']->name }}</td>
                                        <td>
                                            <input type="text" class="form-control" readonly
                                                value="{{ $row['marks']['Milestones'] ?? '—' }}">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" readonly
                                                value="{{ $row['marks']['Project Book'] ?? '—' }}">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" readonly
                                                value="{{ $row['marks']['Presentation'] ?? '—' }}">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control"
                                                name="marks[{{ $sid }}][remarks]"
                                                placeholder="Optional remarks"
                                                value="{{ old("marks.{$sid}.remarks", $row['remarks']) }}">
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No students found in this project's team.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="row" style="padding: 0 15px 15px 15px;">
                        <div class="col-md-12 text-right">
                            <a href="{{ route('teachers.project-marks.show', $project->id) }}"
                               class="btn btn-default">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Save Marks
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection