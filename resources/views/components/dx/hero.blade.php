{{-- chips: [['label' => 'overdue', 'value' => 3, 'alert' => true], ...]  actions: [['label' => 'Milestones', 'url' => '/x', 'ghost' => true], ...] --}}
@props(['title', 'text' => null, 'chips' => [], 'actions' => []])
<div class="dx-hero">
    <div>
        <h1>{{ $title }}</h1>
        @if($text)<p class="dx-hero__text">{{ $text }}</p>@endif
        @if(count($chips))
            <div class="dx-chips">
                @foreach($chips as $chip)
                    <span class="dx-chip {{ !empty($chip['alert']) && $chip['value'] > 0 ? 'dx-chip--alert' : '' }}"><b>{{ $chip['value'] }}</b> {{ $chip['label'] }}</span>
                @endforeach
            </div>
        @endif
    </div>
    @if(count($actions))
        <div class="dx-actions">
            @foreach($actions as $a)
                @if($a['url'])<a class="dx-btn {{ !empty($a['ghost']) ? 'dx-btn--ghost' : '' }}" href="{{ $a['url'] }}">{{ $a['label'] }}</a>@endif
            @endforeach
        </div>
    @endif
</div>
