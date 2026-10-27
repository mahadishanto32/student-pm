{{-- vars: $markCards, $topicMap, $perTopic --}}
@include('dashboard.partials.marks-styles')
<x-dx.panel title="My marks" sub="Grade, percentage and marks per project"
            :href="\Illuminate\Support\Facades\Route::has('student.project-marks.index') ? route('student.project-marks.index') : null">
    @forelse($markCards as $c)
        @php $row = $c['row']; @endphp
        <div class="dxm-project">
            @if($row)
                <div class="dxm-hero {{ \App\Support\MarkUi::tone($row['grade']) }}">
                    <div class="dxm-grade" title="Grade point {{ $row['point'] }}">{{ $row['grade'] }}</div>
                    <div class="dxm-hero__main">
                        <div class="dxm-hero__name">{{ $c['name'] }}</div>
                        <div class="dxm-hero__sub">
                            Group {{ $c['group'] }}
                            @if($row['complete'])· Grade point {{ $row['point'] }}@else· Incomplete: some topics not marked yet @endif
                        </div>
                    </div>
                    <div class="dxm-figures">
                        <div><b>{{ number_format($row['percentage'], 1) }}%</b><span>Percent</span></div>
                        <div><b>{{ \App\Support\MarkUi::fmt($row['total']) }}<small style="font-size:12px">/{{ $row['out_of'] }}</small></b><span>Marks</span></div>
                    </div>
                </div>
                <div class="dxm-topics">
                    @foreach($topicMap as $topic)
                        @php $v = $row['marks'][$topic]; @endphp
                        <div class="dxm-topic">
                            <span>{{ $topic }}</span>
                            <span class="dxm-topic__bar"><i style="width: {{ $v !== null ? $v / $perTopic * 100 : 0 }}%"></i></span>
                            <b>{{ $v !== null ? \App\Support\MarkUi::fmt($v) : '—' }}/{{ $perTopic }}</b>
                        </div>
                    @endforeach
                </div>
                @if($row['remarks'])<div class="dxm-note">Remarks: {{ $row['remarks'] }}</div>@endif
            @else
                <div class="dxm-hero t-i">
                    <div class="dxm-grade">–</div>
                    <div class="dxm-hero__main">
                        <div class="dxm-hero__name">{{ $c['name'] }}</div>
                        <div class="dxm-hero__sub">Group {{ $c['group'] }} · Not marked yet</div>
                    </div>
                </div>
            @endif
        </div>
    @empty
        <x-dx.empty icon="trophy" title="No marks yet" text="They will appear here once you are on a project and your supervisor enters them." />
    @endforelse
</x-dx.panel>