{{-- vars: $title, $sub, $counts (collection status => count), $total label --}}
@php($max = max(1, $counts->max()))
<x-dx.panel :title="$title" :sub="$sub ?? null">
    @if($counts->sum() === 0)
        <x-dx.empty icon="inbox" :title="$emptyTitle ?? 'Nothing here yet'" />
    @else
        @foreach($counts as $status => $count)
            <div class="dx-stat dx-st-{{ $status }}">
                <span>{{ ucwords(str_replace('_', ' ', $status)) }}</span>
                <span class="dx-stat__bar"><i style="width: {{ $count / $max * 100 }}%"></i></span>
                <b>{{ $count }}</b>
            </div>
        @endforeach
    @endif
</x-dx.panel>
