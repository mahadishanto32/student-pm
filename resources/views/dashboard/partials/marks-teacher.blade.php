{{-- vars: $markProjects, $markSheets, $topicMap, $perTopic --}}
@include('dashboard.partials.marks-styles')
<x-dx.panel title="Project marks" sub="Grade, percentage and marks of every student on your projects"
            :href="\Illuminate\Support\Facades\Route::has('teachers.project-marks.index') ? route('teachers.project-marks.index') : null">
    @forelse($markProjects as $project)
        @php
            $rows     = $markSheets[$project->id] ?? collect();
            $complete = $rows->where('complete', true);
            $avg      = $complete->count() ? round($complete->avg('percentage'), 1) : null;
        @endphp
        <div class="dxm-project">
            <div class="dxm-head">
                <h4>
                    {{ $project->project_name }}
                    <small class="text-muted">Group {{ $project->group_number }}</small>
                </h4>
                <span>
                    <small class="text-muted">
                        {{ $complete->count() }}/{{ $rows->count() }} fully marked{{ $avg !== null ? ' · avg ' . $avg . '%' : '' }}
                    </small>
                    @if(\Illuminate\Support\Facades\Route::has('teachers.project-marks.edit'))
                        <a href="{{ route('teachers.project-marks.edit', $project->id) }}" class="btn btn-xs btn-primary" title="Edit marks">
                            <i class="fa fa-pencil"></i>
                        </a>
                    @endif
                </span>
            </div>
            <div class="table-responsive">
                <table class="dxm-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            @foreach($topicMap as $topic)<th>{{ $topic }}</th>@endforeach
                            <th>Total /{{ $perTopic * count($topicMap) }}</th>
                            <th>%</th>
                            <th>Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td>{{ $row['student']->name }}</td>
                                @foreach($topicMap as $topic)
                                    <td>{{ $row['marks'][$topic] !== null ? \App\Support\MarkUi::fmt($row['marks'][$topic]) : '—' }}</td>
                                @endforeach
                                <td><b>{{ \App\Support\MarkUi::fmt($row['total']) }}</b></td>
                                <td><b>{{ number_format($row['percentage'], 1) }}%</b></td>
                                <td><span class="dxm-pill {{ \App\Support\MarkUi::tone($row['grade']) }}">{{ $row['grade'] }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ 4 + count($topicMap) }}" class="text-muted">No students in this project.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <x-dx.empty icon="trophy" title="No assigned projects yet" />
    @endforelse
    <div class="dxm-note" style="padding:0">Grade <b>I</b> = Incomplete (not every topic is marked yet).</div>
</x-dx.panel>