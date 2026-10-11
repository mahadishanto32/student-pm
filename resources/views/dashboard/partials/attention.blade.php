{{-- vars: $attention (open milestones), $href --}}
<x-dx.panel title="Milestones to act on" sub="Overdue first, then by due date" :href="$href ?? null">
    @if($attention->isEmpty())
        <x-dx.empty icon="check-circle-o" title="All caught up" text="No pending or correction-needed milestones." />
    @else
        <ul class="dx-list">
            @foreach($attention as $m)
                @php
                    $due  = $m->tentative_time;
                    $late = $due && $due->isPast();
                    $cls  = $late ? 'is-late' : ($due && $due->isToday() ? 'is-today' : '');
                @endphp
                <li>
                    <span class="dx-dot dx-st-{{ $m->status }}" title="{{ ucwords(str_replace('_', ' ', $m->status)) }}"></span>
                    <div class="dx-list__main">
                        <div class="dx-list__title">{{ $m->title }}</div>
                        <div class="dx-list__sub">{{ $m->project->project_name ?? 'N/A' }}</div>
                    </div>
                    <span class="dx-when {{ $cls }}">
                        @if(!$due)No date
                        @elseif($late)Overdue<br>{{ $due->diffForHumans() }}
                        @elseif($due->isToday())Today<br>{{ $due->format('g:i A') }}
                        @else{{ $due->format('j M') }}<br>{{ $due->diffForHumans() }}@endif
                    </span>
                </li>
            @endforeach
        </ul>
    @endif
</x-dx.panel>
