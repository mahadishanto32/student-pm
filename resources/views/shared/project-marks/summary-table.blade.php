@php
    use App\Models\MarksDistribution;

    $fmt = fn ($n) => $n === null ? '—' : rtrim(rtrim(number_format((float) $n, 2), '0'), '.');

    $showTeacher = $showTeacher ?? false;
    $canEdit     = $canEdit ?? false;

    // Summary view: Milestones only
    $milestoneMax = MarksDistribution::TOPIC_MAX['Milestones'];   // 100
    $grandTotal   = MarksDistribution::TOTAL_MARKS;               // 200

    // Columns: #, Group, Project, [Teacher], Students, Milestones avg, Overall %, Fully Marked, Actions
    $colCount = 3
              + ($showTeacher ? 1 : 0)
              + 1   // Students
              + 1   // Milestones avg
              + 1   // Overall %
              + 1   // Fully Marked
              + 1;  // Actions
@endphp

<div class="table-responsive" style="padding: 15px;">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Group</th>
                <th>Project</th>
                @if ($showTeacher)
                    <th>Teacher</th>
                @endif
                <th>Students</th>
                <th>
                    Milestones<br>
                    <small class="text-muted">avg / {{ $milestoneMax }}</small>
                </th>
                <th>Overall %</th>
                <th>Fully Marked</th>
                <th style="width: 110px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($projects as $project)
                @php
                    $s     = $summaries[$project->id] ?? null;
                    $names = $s['student_names'] ?? [];
                @endphp
                <tr>
                    <td>{{ $loop->iteration + ($projects->currentPage() - 1) * $projects->perPage() }}</td>
                    <td>{{ $project->group_number ?? '—' }}</td>
                    <td>
                        {{ $project->project_name ?? '—' }}
                        @if ($project->project_topic)
                            <br><small class="text-muted">{{ $project->project_topic }}</small>
                        @endif
                    </td>
                    @if ($showTeacher)
                        <td>{{ $project->teacher->name ?? '—' }}</td>
                    @endif

                    <td>
                        @if (count($names))
                            @foreach ($names as $name)
                                <div>{{ $name }}</div>
                            @endforeach
                        @else
                            —
                        @endif
                    </td>

                    <td>
                        {{ $fmt($s['milestones_avg'] ?? null) }}
                        @if (($s['milestones_marked'] ?? 0) > 0)
                            <br><small class="text-muted">({{ $s['milestones_marked'] }} marked)</small>
                        @endif
                    </td>

                    <td>{{ $s && ($s['avg_pct'] ?? null) !== null ? number_format($s['avg_pct'], 1) . '%' : '—' }}</td>

                    <td>
                        @if ($s)
                            <span class="label {{ $s['students'] > 0 && $s['complete'] === $s['students'] ? 'label-success' : 'label-warning' }}">
                                {{ $s['complete'] }} / {{ $s['students'] }}
                            </span>
                        @else
                            —
                        @endif
                    </td>

                    <td>
                        <a href="{{ route($routePrefix . '.show', $project->id) }}"
                           class="btn btn-sm btn-default" title="View">
                            <i class="fa fa-eye"></i>
                        </a>
                        @if ($canEdit)
                            <a href="{{ route($routePrefix . '.edit', $project->id) }}"
                               class="btn btn-sm btn-primary" title="Edit marks">
                                <i class="fa fa-pencil"></i>
                            </a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $colCount }}" class="text-center">No projects found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <small class="text-muted">
        Milestones avg is scaled to /{{ $milestoneMax }} from completed milestone marks.
        Overall % is the full total (out of {{ $grandTotal }}) averaged over fully-marked students.
    </small>
</div>