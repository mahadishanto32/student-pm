@props(['icon', 'value', 'label', 'note' => null, 'tone' => 'blue'])
<div class="dx-kpi dx-tone-{{ $tone }}">
    <span class="dx-kpi__icon" aria-hidden="true"><i class="fa fa-{{ $icon }}"></i></span>
    <div>
        <div class="dx-kpi__value">{{ $value }}</div>
        <div class="dx-kpi__label">{{ $label }}</div>
        @if($note)<div class="dx-kpi__note">{{ $note }}</div>@endif
    </div>
</div>
