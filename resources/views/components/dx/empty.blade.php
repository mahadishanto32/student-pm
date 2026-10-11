@props(['icon' => 'inbox', 'title', 'text' => null])
<div class="dx-empty">
    <i class="fa fa-{{ $icon }}" aria-hidden="true"></i>
    <b>{{ $title }}</b>
    @if($text)<span>{{ $text }}</span>@endif
</div>
