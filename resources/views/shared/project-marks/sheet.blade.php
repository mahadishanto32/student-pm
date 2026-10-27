@php
    $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2), '0'), '.');
    $gradeBadge = fn ($g) => match (true) {
        in_array($g, ['A+', 'A', 'A-']) => 'label-success',
        in_array($g, ['B+', 'B', 'B-']) => 'label-info',
        in_array($g, ['C+', 'C', 'D'])  => 'label-warning',
        $g === 'F'                      => 'label-danger',
        default                         => 'label-default',
    };
    $showSupervisor = $showSupervisor ?? false;
@endphp

<div class="table-responsive" style="padding: 15px;">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th style="width: 50px;">#</th>
                <th>Student</th>
                @foreach ($topicMap as $topic)
                    <th>{{ $topic }}<br><small class="text-muted">/ {{ $perTopic }}</small></th>
                @endforeach
                <th>Total<br><small class="text-muted">/ {{ $perTopic * count($topicMap) }}</small></th>
                <th>%</th>
                <th>Grade</th>
                <th>Remarks</th>
                @if ($showSupervisor)
                    <th>Marked By</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $row['student']->name }}</td>
                    @foreach ($topicMap as $topic)
                        <td>
                            @if ($row['marks'][$topic] !== null)
                                <strong>{{ $fmt($row['marks'][$topic]) }}</strong>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    @endforeach
                    <td><strong>{{ $fmt($row['total']) }}</strong></td>
                    <td>{{ number_format($row['percentage'], 1) }}%</td>
                    <td>
                        <span class="label {{ $gradeBadge($row['grade']) }}">{{ $row['grade'] }}</span>
                        <small class="text-muted">({{ $row['point'] }})</small>
                    </td>
                    <td>{{ $row['remarks'] ?: '—' }}</td>
                    @if ($showSupervisor)
                        <td>{{ $row['supervisor'] ?? '—' }}</td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 7 + count($topicMap) - 1 + ($showSupervisor ? 1 : 0) }}" class="text-center">
                        No students found in this project's team.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <small class="text-muted">Grade <strong>I</strong> = Incomplete (not every topic has been marked yet).</small>
</div>