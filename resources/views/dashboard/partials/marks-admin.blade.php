{{-- vars: $markCards, $gradeDist, $markStudents, $markGraded, $markOutOf --}}
@include('dashboard.partials.marks-styles')
<x-dx.panel title="Project marks"
            :sub="$markGraded . ' of ' . $markStudents . ' students fully marked. Top projects by average'"
            :href="\Illuminate\Support\Facades\Route::has('admin.project-marks.index') ? route('admin.project-marks.index') : null">

    <div class="dxm-dist">
        @foreach($gradeDist as $grade => $count)
            <span><i class="dxm-pill {{ \App\Support\MarkUi::tone($grade) }}">{{ $grade }}</i> <b>{{ $count }}</b></span>
        @endforeach
    </div>

    <div class="row">
        @forelse($markCards as $c)
            <div class="col-md-6 col-xl-3">
                <div class="dxm-project">
                    <div class="dxm-hero {{ \App\Support\MarkUi::tone($c['grade']) }}" style="padding:14px">
                        <div class="dxm-grade" style="width:54px;height:54px;font-size:20px">{{ $c['grade'] }}</div>
                        <div class="dxm-hero__main">
                            <div class="dxm-hero__name">{{ \Illuminate\Support\Str::limit($c['name'], 26) }}</div>
                            <div class="dxm-hero__sub">Group {{ $c['group'] }}</div>
                        </div>
                    </div>
                    <div class="dxm-topics" style="padding:10px 14px">
                        <div class="dxm-topic" style="grid-template-columns:1fr auto"><span>Average</span><b>{{ $c['avg'] !== null ? $c['avg'] . '%' : '—' }}</b></div>
                        <div class="dxm-topic" style="grid-template-columns:1fr auto"><span>Avg marks</span><b>{{ $c['total'] !== null ? \App\Support\MarkUi::fmt($c['total']) . '/' . $markOutOf : '—' }}</b></div>
                        <div class="dxm-topic" style="grid-template-columns:1fr auto"><span>Fully marked</span><b>{{ $c['complete'] }}/{{ $c['students'] }}</b></div>
                    </div>
                    <div class="dxm-note" style="padding:0 14px 10px">Supervisor: {{ $c['teacher'] ?? 'none' }}</div>
                </div>
            </div>
        @empty
            <div class="col-12"><x-dx.empty icon="trophy" title="No projects yet" /></div>
        @endforelse
    </div>
    <div class="dxm-note" style="padding:0">
        Project grade is based on students marked in all three topics. <b>I</b> = Incomplete.
    </div>
</x-dx.panel>