@extends('layouts.app')

@section('title', 'Project Marks')

@section('content')
    @php
        use App\Models\MarksDistribution;

        $fmt = fn ($n) => $n === null ? '—' : rtrim(rtrim(number_format((float) $n, 2), '0'), '.');
        $gradeBadge = fn ($g) => match (true) {
            in_array($g, ['A+', 'A', 'A-']) => 'label-success',
            in_array($g, ['B+', 'B', 'B-']) => 'label-info',
            in_array($g, ['C+', 'C', 'D'])  => 'label-warning',
            $g === 'F'                      => 'label-danger',
            default                         => 'label-default',
        };
        $topicMax   = MarksDistribution::TOPIC_MAX;
        $grandTotal = MarksDistribution::TOTAL_MARKS;
    @endphp

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

                <div class="row" style="padding: 15px 15px 0 15px;">
                    <div class="col-md-12">
                        <h4>My Marks by Project</h4>
                        <p class="text-muted" style="margin-bottom: 0;">
                            Milestones are shown as soon as they are marked.
                            Project Book ({{ $topicMax['Project Book'] }}) and
                            Presentation ({{ $topicMax['Presentation'] }}) are revealed
                            once the project is <strong>completed</strong>.
                            Total is out of <strong>{{ $grandTotal }}</strong>.
                        </p>
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
                                    <th>
                                        {{ $topic }}<br>
                                        <small class="text-muted">/ {{ $topicMax[$topic] }}</small>
                                    </th>
                                @endforeach
                                <th>Total<br><small class="text-muted">/ {{ $grandTotal }}</small></th>
                                <th>%</th>
                                <th>Grade</th>
                                <th style="width: 70px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($projects as $project)
                                @php
                                    $row     = ($sheets[$project->id] ?? collect())->first();
                                    $visible = $visibleTopics[$project->id] ?? ['Milestones'];
                                    $locked  = collect($topicMap)->keys()
                                        ->filter(fn ($k) => ! in_array($topicMap[$k], $visible, true))
                                        ->all();
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $project->group_number ?? '—' }}</td>
                                    <td>
                                        {{ $project->project_name ?? '—' }}
                                        @if ($project->project_topic)
                                            <br><small class="text-muted">{{ $project->project_topic }}</small>
                                        @endif
                                        @if (($project->status ?? null) !== 'completed')
                                            <br><small class="text-info">
                                                <i class="fa fa-lock"></i> Book &amp; Presentation locked until project completion
                                            </small>
                                        @endif
                                    </td>
                                    @foreach ($topicMap as $key => $topic)
                                        <td>
                                            @if (in_array($topic, $visible, true))
                                                @if ($row && $row['marks'][$topic] !== null)
                                                    <strong>{{ $fmt($row['marks'][$topic]) }}</strong>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            @else
                                                <span class="text-muted"><i class="fa fa-lock"></i> Hidden</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td>
                                        @if ($row)
                                            {{-- Show partial total when not all topics visible --}}
                                            @if (count($locked) === 0)
                                                <strong>{{ $fmt($row['total']) }}</strong>
                                            @else
                                                <strong>{{ $fmt($row['marks']['Milestones'] ?? 0) }}</strong>
                                                <small class="text-muted">/ {{ $topicMax['Milestones'] }} so far</small>
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        @if ($row && count($locked) === 0)
                                            {{ number_format($row['percentage'], 1) }}%
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($row && count($locked) === 0 && $row['grade'] !== 'I')
                                            <span class="label {{ $gradeBadge($row['grade']) }}">{{ $row['grade'] }}</span>
                                            <small class="text-muted">({{ $row['point'] }})</small>
                                        @elseif ($row && count($locked) > 0)
                                            <span class="label label-default">Pending</span>
                                        @elseif ($row)
                                            <span class="label label-default">I</span>
                                            <small class="text-muted">(-)</small>
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
                    <small class="text-muted">
                        Grade <strong>I</strong> = Incomplete (not every topic has been marked yet).
                        <strong>%</strong>, <strong>Grade</strong>, and the full <strong>Total / {{ $grandTotal }}</strong>
                        are only shown once the project is completed.
                    </small>
                </div>

            </div>
        </div>
    </div>
@endsection