@extends('layouts.app')

@section('title', 'Project Marks')

@section('content')
    <div class="row column_title">
        <div class="col-md-12">
            <div class="page_title">
                <h2>Project Marks</h2>
            </div>
        </div>
    </div>

    <div class="row column1">
        <div class="col-md-12">
            <div class="full white_shadow_bg margin_bottom_30">

                @php
                    $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2), '0'), '.');
                    $gradeBadge = fn ($g) => match (true) {
                        in_array($g, ['A+', 'A', 'A-']) => 'label-success',
                        in_array($g, ['B+', 'B', 'B-']) => 'label-info',
                        in_array($g, ['C+', 'C', 'D'])  => 'label-warning',
                        $g === 'F'                      => 'label-danger',
                        default                         => 'label-default',
                    };
                @endphp

                <div class="row" style="padding: 15px 15px 0 15px;">
                    <div class="col-md-12">
                        <h4>My Marks by Project</h4>
                    </div>
                </div>

                <div class="table-responsive" style="padding: 15px;">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Group</th>
                                <th>Project</th>
                                @foreach ($topicMap as $topic)
                                    <th>{{ $topic }}<br><small class="text-muted">/ {{ $perTopic }}</small></th>
                                @endforeach
                                <th>Total<br><small class="text-muted">/ {{ $perTopic * count($topicMap) }}</small></th>
                                <th>%</th>
                                <th>Grade</th>
                                <th style="width: 70px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($projects as $project)
                                @php $row = ($sheets[$project->id] ?? collect())->first(); @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $project->group_number ?? '—' }}</td>
                                    <td>
                                        {{ $project->project_name ?? '—' }}
                                        @if ($project->project_topic)
                                            <br><small class="text-muted">{{ $project->project_topic }}</small>
                                        @endif
                                    </td>
                                    @foreach ($topicMap as $topic)
                                        <td>
                                            @if ($row && $row['marks'][$topic] !== null)
                                                <strong>{{ $fmt($row['marks'][$topic]) }}</strong>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td>{{ $row ? $fmt($row['total']) : '—' }}</td>
                                    <td>{{ $row ? number_format($row['percentage'], 1) . '%' : '—' }}</td>
                                    <td>
                                        @if ($row)
                                            <span class="label {{ $gradeBadge($row['grade']) }}">{{ $row['grade'] }}</span>
                                            <small class="text-muted">({{ $row['point'] }})</small>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('student.project-marks.show', $project->id) }}"
                                           class="btn btn-sm btn-default" title="View">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 7 + count($topicMap) }}" class="text-center">
                                        You are not part of any project yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <small class="text-muted">Grade <strong>I</strong> = Incomplete (not every topic has been marked yet).</small>
                </div>

            </div>
        </div>
    </div>
@endsection