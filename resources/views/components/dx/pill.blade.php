@props(['status'])
<span class="dx-pill dx-st-{{ $status }}">{{ ucwords(str_replace('_', ' ', $status)) }}</span>
