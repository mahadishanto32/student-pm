{{-- Usage: @include('partials.notification-bell', ['prefix' => 'admin']) --}}
@php
    $unreadCount = auth()->user()->unreadNotifications()->count();
    $latest = auth()->user()->notifications()->latest()->take(8)->get();
@endphp

@once
<style>
    /* Pinned to the viewport so template CSS / Popper can't push it off-screen */
    .notif-menu {
        position: fixed !important;
        top: 70px !important;            /* adjust to your topbar height */
        right: 10px !important;
        left: auto !important;
        bottom: auto !important;
        transform: none !important;
        margin: 0 !important;
        width: 360px !important;
        max-width: calc(100vw - 20px) !important;
        max-height: calc(100vh - 90px);
        overflow-y: auto;
        overflow-x: hidden;
        padding: 0;
    }
    .notif-menu .notif-head {
        font-size: 15px;
    }
    .notif-menu .notif-item {
        white-space: normal !important;
        overflow: visible !important;
        text-overflow: clip !important;
        word-wrap: break-word;
        overflow-wrap: anywhere;
        border-bottom: 1px solid #eee;
        padding: 10px 16px;
        line-height: 1.35;
    }
    .notif-menu .notif-title   { font-size: 14px; font-weight: 600; margin: 0 0 2px; }
    .notif-menu .notif-message { font-size: 13px; color: #6c757d; display: block; }
    .notif-menu .notif-time    { font-size: 12px; color: #999; display: block; margin-top: 2px; }
    .notif-menu .notif-item.unread { background: #f1f7ff; }
</style>
@endonce

<li class="dropdown">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <i class="fa fa-bell-o"></i>
        @if ($unreadCount)
            <span class="badge">{{ $unreadCount }}</span>
        @endif
    </a>

    <div class="dropdown-menu notif-menu">
        <div class="d-flex justify-content-between align-items-center px-3 py-2 notif-head">
            <strong>Notifications</strong>
            @if ($unreadCount)
                <form method="POST" action="{{ route($prefix.'.notifications.readAll') }}" class="m-0">
                    @csrf
                    <button class="btn btn-link btn-sm p-0">Mark all read</button>
                </form>
            @endif
        </div>
        <div class="dropdown-divider m-0"></div>

        @forelse ($latest as $n)
            <a class="dropdown-item notif-item {{ $n->read_at ? '' : 'unread' }}"
               href="{{ route($prefix.'.notifications.read', $n->id) }}">
                <div class="notif-title">{{ $n->data['title'] ?? 'Notification' }}</div>
                <span class="notif-message">{{ $n->data['message'] ?? '' }}</span>
                <span class="notif-time">{{ $n->created_at->diffForHumans() }}</span>
            </a>
        @empty
            <span class="dropdown-item text-muted py-3">No notifications</span>
        @endforelse
    </div>
</li>
