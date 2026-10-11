@php
    $colors = ['pending' => '#a8690a', 'need_correction' => '#c24f25', 'completed' => '#1f8a63', 'rejected' => '#a53c52'];
    $total  = $milestoneStats->sum('count');
@endphp
<x-dx.panel title="Milestone status" :sub="$total . ' in total'">
    @if($total === 0)
        <x-dx.empty icon="flag-o" title="No milestones yet" text="Status will appear here once milestones are added." />
    @else
        <div class="dx-donut">
            <canvas id="dxStatus" role="img" aria-label="Milestones by status"></canvas>
            <div class="dx-donut__centre"><b>{{ $stats['completion'] }}%</b><span>completed</span></div>
        </div>
        <ul class="dx-legend">
            @foreach($milestoneStats as $s)
                <li class="dx-st-{{ $s['status'] }}">
                    <span><i></i>{{ $s['label'] }}</span>
                    <b>{{ $s['count'] }} <small class="text-muted">({{ $s['percent'] }}%)</small></b>
                </li>
            @endforeach
        </ul>
        <script>
        (function () {
            var el = document.getElementById('dxStatus');
            if (!el || typeof Chart === 'undefined') return;
            var colors = @json($colors);
            var keys = @json($milestoneStats->pluck('status'));
            new Chart(el, {
                type: 'doughnut',
                data: { labels: @json($milestoneStats->pluck('label')), datasets: [{ data: @json($milestoneStats->pluck('count')), backgroundColor: keys.map(function (k) { return colors[k]; }), borderWidth: 2 }] },
                options: { cutout: '70%', plugins: { legend: { display: false } } }
            });
        })();
        </script>
    @endif
</x-dx.panel>
