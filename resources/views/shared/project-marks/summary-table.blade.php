@php
    $fmt = fn ($n) => $n === null ? '—' : rtrim(rtrim(number_format((float) $n, 2), '0'), '.');
    $showTeacher = $showTeacher ?? false;
    $canEdit     = $canEdit ?? false;
    $perTopic    = \App\Models\ProjectMark::MARKS_PER_TOPIC;
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
                <th>Milestones<br><small class="text-muted">avg / {{ $perTopic }}</small></th>
                <th>Project Book<br><small class="text-muted">avg / {{ $perTopic }}</small></th>
                <th>Presentation<br><small class="text-muted">avg / {{ $perTopic }}</small></th>
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

                    {{-- Students: one name per line --}}
                    <td>
                        @if (count($names))
                            @foreach ($names as $name)
                                <div>{{ $name }}</div>
                            @endforeach
                        @else
                            —
                        @endif
                    </td>

                    @foreach (['Milestones', 'Project Book', 'Presentation'] as $topic)
                        <td>{{ $fmt($s['topic_avg'][$topic] ?? null) }}</td>
                    @endforeach
                    <td>{{ $s && $s['avg_pct'] !== null ? $s['avg_pct'] . '%' : '—' }}</td>
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
                    <td colspan="{{ $showTeacher ? 11 : 10 }}" class="text-center">No projects found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <small class="text-muted">Overall % is averaged over students who have marks in all three topics.</small>
</div>