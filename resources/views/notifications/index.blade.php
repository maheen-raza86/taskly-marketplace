@extends('layouts.app')

@section('title', 'Notifications')

@section('content')

<div class="page-wrap">

    <div class="page-header">
        <div>
            <h1>Notifications</h1>
            @php $unreadCount = $notifications->getCollection()->where('is_read', false)->count(); @endphp
            @if($unreadCount > 0)
                <span class="sub" style="margin-left:.5rem;">
                    {{ $unreadCount }} unread on this page
                </span>
            @endif
        </div>
        @if($unreadCount > 0)
            @php
                $markAllRoute = match(auth()->user()->role) {
                    'admin'    => route('admin.notifications.readAll'),
                    'provider' => route('provider.notifications.readAll'),
                    default    => route('customer.notifications.readAll'),
                };
            @endphp
            <form action="{{ $markAllRoute }}" method="POST">
                @csrf
                <button type="submit" class="btn-ghost" style="font-size:.82rem;padding:.38rem .9rem;">
                    ✓ Mark all as read
                </button>
            </form>
        @endif
    </div>

    @if($notifications->count())
        <div class="card" style="padding:0;overflow:hidden;">
            <ul class="notif-list" style="padding:0;">
                @foreach($notifications as $notif)
                    <li class="notif-item" style="padding:1rem 1.25rem;{{ $notif->is_read ? '' : 'background:#fafbff;' }}">

                        {{-- Type icon --}}
                        <span style="font-size:1.2rem;flex-shrink:0;margin-top:.05rem;">
                            @switch($notif->type)
                                @case('booking_request')   🔔 @break
                                @case('booking_confirmed') ✅ @break
                                @case('booking_cancelled') ❌ @break
                                @case('booking_completed') 🎉 @break
                                @case('new_review')        ⭐ @break
                                @case('account_approved')  🎊 @break
                                @case('account_rejected')  ⛔ @break
                                @default                   📩
                            @endswitch
                        </span>

                        <div class="notif-body">
                            <div class="notif-title" style="{{ $notif->is_read ? 'font-weight:500;color:#374151;' : '' }}">
                                {{ $notif->title }}
                                @if(!$notif->is_read)
                                    <span style="display:inline-block;width:7px;height:7px;background:#6366f1;border-radius:50%;margin-left:.4rem;vertical-align:middle;"></span>
                                @endif
                            </div>
                            <div class="notif-msg">{{ $notif->message }}</div>
                            <div class="notif-time">{{ $notif->created_at->diffForHumans() }}</div>
                        </div>

                        @if(!$notif->is_read)
                            @php
                                $markRoute = match(auth()->user()->role) {
                                    'admin'    => route('admin.notifications.read', $notif),
                                    'provider' => route('provider.notifications.read', $notif),
                                    default    => route('customer.notifications.read', $notif),
                                };
                            @endphp
                            <div class="notif-actions">
                                <form action="{{ $markRoute }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn-mark-read">Mark read</button>
                                </form>
                            </div>
                        @else
                            <span style="font-size:.7rem;color:#d1d5db;flex-shrink:0;">✓ Read</span>
                        @endif

                    </li>
                @endforeach
            </ul>
        </div>

        @if($notifications->hasPages())
            <div class="pagination-wrap" style="margin-top:1.25rem;">
                {{ $notifications->links() }}
            </div>
        @endif

    @else
        <div class="empty-state">
            <div class="icon" style="font-size:3rem;">🔔</div>
            <p>You're all caught up — no notifications yet.</p>
        </div>
    @endif

</div>

@endsection
