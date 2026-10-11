{{-- vars: $schedule, $href (optional) --}}
<x-dx.panel title="Coming up" sub="Meetings and presentations" :href="$href ?? null">
    @if($schedule->isEmpty())
        <x-dx.empty icon="calendar-o" title="Nothing scheduled" text="Upcoming meetings and presentations will show here." />
    @else
        <ul class="dx-list">
            @foreach($schedule as $item)
                <li>
                    <span class="dx-date"><b>{{ $item['at']->format('j') }}</b><span>{{ $item['at']->format('M') }}</span></span>
                    <div class="dx-list__main">
                        <div class="dx-list__title">{{ $item['title'] }}</div>
                        <div class="dx-list__sub">{{ $item['project'] }}</div>
                        <div class="dx-list__sub">
                            <i class="fa fa-{{ $item['kind'] === 'meeting' ? 'users' : 'desktop' }}" aria-hidden="true"></i>
                            {{ ucfirst($item['kind']) }}, {{ $item['meta'] }}{{ $item['at']->format('H:i') !== '00:00' ? ', ' . $item['at']->format('g:i A') : '' }}
                        </div>
                    </div>
                    <span class="dx-when {{ $item['at']->isToday() ? 'is-today' : '' }}">{{ $item['at']->isToday() ? 'Today' : $item['at']->diffForHumans() }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</x-dx.panel>
