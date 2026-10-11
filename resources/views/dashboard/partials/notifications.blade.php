{{-- vars: $notifications ['unread' => int, 'items' => collection] --}}
<x-dx.panel title="Notifications" :sub="$notifications['unread'] . ' unread'">
    @if($notifications['items']->isEmpty())
        <x-dx.empty icon="bell-o" title="You're up to date" text="New project and meeting alerts will appear here." />
    @else
        <ul class="dx-list">
            @foreach($notifications['items'] as $n)
                <li>
                    <span class="dx-icon"><i class="fa fa-bell-o" aria-hidden="true"></i></span>
                    <div class="dx-list__main">
                        <div class="dx-list__title">{{ $n->data['title'] ?? 'Notification' }}</div>
                        <div class="dx-list__sub">{{ $n->data['message'] ?? '' }}</div>
                    </div>
                    <span class="dx-when">{{ $n->created_at->diffForHumans() }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</x-dx.panel>
