@php($id = 'dxActivity')
<x-dx.panel :title="$title ?? 'Milestone activity'" sub="Last 7 months">
    <div class="dx-chart"><canvas id="{{ $id }}" role="img" aria-label="Milestones created and completed per month"></canvas></div>
</x-dx.panel>
<script>
(function () {
    var el = document.getElementById('{{ $id }}');
    if (!el || typeof Chart === 'undefined') return;
    var datasets = [
        { type: 'bar',  label: 'Milestones created',   data: @json($chart['created']),   backgroundColor: '#c5d3e6', borderRadius: 4, order: 3 },
        { type: 'line', label: 'Milestones completed', data: @json($chart['completed']), borderColor: '#1f8a63', backgroundColor: '#1f8a63', tension: .3, pointRadius: 3, order: 1 }
    ];
    @isset($chart['projects'])
    datasets.push({ type: 'line', label: 'Projects created', data: @json($chart['projects']), borderColor: '#2c6db5', backgroundColor: '#2c6db5', borderDash: [5, 4], tension: .3, pointRadius: 3, order: 2 });
    @endisset
    new Chart(el, {
        data: { labels: @json($chart['labels']), datasets: datasets },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#eef1f5' } }, x: { grid: { display: false } } }
        }
    });
})();
</script>
