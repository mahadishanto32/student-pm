@extends('admin.layouts.app')

@section('title', 'Edit Project Marks')

@section('content')
    @php
        use App\Models\MarksDistribution;
        $topicMax = MarksDistribution::TOPIC_MAX;
    @endphp

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
                        <p class="text-muted" style="margin-bottom: 0;">
                            Supervisor: <strong>{{ $project->teacher->name ?? '—' }}</strong>
                        </p>
                    </div>
                </div>

                <div class="alert alert-info" style="margin: 15px;">
                    <strong>Milestones</strong> ({{ $topicMax['Milestones'] }} marks) are set automatically from completed milestone marks entered by the teacher
                    and cannot be edited here.
                    <strong>Project Book</strong> ({{ $topicMax['Project Book'] }}) and
                    <strong>Presentation</strong> ({{ $topicMax['Presentation'] }}) can be edited.
                    Leave a field blank to clear it.
                </div>

                <form action="{{ route('admin.project-marks.update', $project->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="table-responsive" style="padding: 15px;">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Student</th>

                                    {{-- Milestones: read-only --}}
                                    <th style="width: 130px;">
                                        Milestones<br>
                                        <small class="text-muted">/ {{ $topicMax['Milestones'] }} (auto)</small>
                                    </th>

                                    {{-- Project Book: editable --}}
                                    <th style="width: 130px;">
                                        Project Book<br>
                                        <small class="text-muted">0 – {{ $topicMax['Project Book'] }}</small>
                                    </th>

                                    {{-- Presentation: editable --}}
                                    <th style="width: 130px;">
                                        Presentation<br>
                                        <small class="text-muted">0 – {{ $topicMax['Presentation'] }}</small>
                                    </th>

                                    <th style="width: 90px;">Total<br>
                                        <small class="text-muted">/ {{ MarksDistribution::TOTAL_MARKS }}</small>
                                    </th>

                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($rows as $row)
                                    @php
                                        $sid = $row['student']->id;
                                        $book = $row['marks']['Project Book'] ?? null;
                                        $pres = $row['marks']['Presentation'] ?? null;
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $row['student']->name }}</td>

                                        {{-- Milestones: lock it visually --}}
                                        <td>
                                            <input type="text" class="form-control" readonly
                                                   value="{{ $row['marks']['Milestones'] !== null
                                                            ? rtrim(rtrim(number_format((float) $row['marks']['Milestones'], 2), '0'), '.')
                                                            : '—' }}"
                                                   title="Auto-computed from completed milestone marks">
                                        </td>

                                        {{-- Project Book: editable --}}
                                        <td>
                                            <input type="number" class="form-control"
                                                   name="marks[{{ $sid }}][project_book]"
                                                   min="0" max="{{ $topicMax['Project Book'] }}" step="0.25"
                                                   value="{{ old("marks.{$sid}.project_book", $book) }}">
                                        </td>

                                        {{-- Presentation: editable --}}
                                        <td>
                                            <input type="number" class="form-control"
                                                   name="marks[{{ $sid }}][presentation]"
                                                   min="0" max="{{ $topicMax['Presentation'] }}" step="0.25"
                                                   value="{{ old("marks.{$sid}.presentation", $pres) }}">
                                        </td>

                                        <td>
                                            <strong>{{ rtrim(rtrim(number_format((float) $row['total'], 2), '0'), '.') }}</strong>
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
                                        <td colspan="7" class="text-center">
                                            No students found in this project's team.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="row" style="padding: 0 15px 15px 15px;">
                        <div class="col-md-12 text-right">
                            <a href="{{ route('admin.project-marks.show', $project->id) }}"
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