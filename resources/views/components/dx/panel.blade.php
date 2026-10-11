@props(['title', 'sub' => null, 'href' => null, 'action' => 'View all'])
<section {{ $attributes->merge(['class' => 'dx-panel']) }}>
    <header class="dx-panel__head">
        <div>
            <h3>{{ $title }}</h3>
            @if($sub)<p>{{ $sub }}</p>@endif
        </div>
        @if($href)<a class="dx-link" href="{{ $href }}">{{ $action }}</a>@endif
    </header>
    <div class="dx-panel__body">{{ $slot }}</div>
</section>
