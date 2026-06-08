{{-- resources/views/layouts/navbar.blade.php --}}
@php
    // Compute unread count and recent notifications once for authenticated users
    if (auth()->check()) {
        $navUnread   = auth()->user()->notifications()->where('is_read', false)->count();
        $navRecent   = auth()->user()->notifications()->latest()->take(5)->get();
        $navAllRoute = match(auth()->user()->role) {
            'admin'    => route('admin.notifications'),
            'provider' => route('provider.notifications'),
            default    => route('customer.notifications'),
        };
        $navReadAllRoute = match(auth()->user()->role) {
            'admin'    => route('admin.notifications.readAll'),
            'provider' => route('provider.notifications.readAll'),
            default    => route('customer.notifications.readAll'),
        };
    }
@endphp

<nav class="navbar">
    <a href="{{ route('home') }}" class="navbar-brand">
        Taskly<span class="brand-dot">.</span>
    </a>

    <ul class="navbar-links">

        {{-- Browse services (always visible) --}}
        <li><a href="{{ route('services.index') }}">Browse Services</a></li>

        @guest
            <li><a href="{{ route('login') }}">Sign In</a></li>
            <li><a href="{{ route('register') }}" class="nav-btn">Get Started</a></li>
        @endguest

        @auth
            {{-- Role-specific primary link --}}
            @if(auth()->user()->role === 'admin')
                <li><a href="{{ route('admin.dashboard') }}">Admin Panel</a></li>
            @elseif(auth()->user()->role === 'provider')
                <li><a href="{{ route('provider.dashboard') }}">Dashboard</a></li>
                <li><a href="{{ route('provider.bookings') }}">Bookings</a></li>
            @else
                <li><a href="{{ route('customer.bookings') }}">My Bookings</a></li>
            @endif

            {{-- ── Notification bell dropdown ──────────────────── --}}
            <li class="notif-dropdown-wrap" id="notifWrap">

                {{-- Bell button --}}
                <button
                    class="notif-bell"
                    id="notifBell"
                    aria-label="Notifications"
                    aria-expanded="false"
                    aria-controls="notifPanel"
                    onclick="toggleNotifPanel(event)"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                         aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @if($navUnread > 0)
                        <span class="notif-badge" id="notifBadge">
                            {{ $navUnread > 9 ? '9+' : $navUnread }}
                        </span>
                    @else
                        <span class="notif-badge notif-badge-hidden" id="notifBadge"></span>
                    @endif
                </button>

                {{-- Dropdown panel --}}
                <div class="notif-panel" id="notifPanel" role="dialog" aria-label="Notifications">

                    <div class="notif-panel-hdr">
                        <span class="notif-panel-title">Notifications</span>
                        @if($navUnread > 0)
                            <form action="{{ $navReadAllRoute }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="notif-mark-all">Mark all read</button>
                            </form>
                        @endif
                    </div>

                    @if($navRecent->count())
                        <ul class="notif-panel-list">
                            @foreach($navRecent as $notif)
                                <li class="notif-panel-item {{ $notif->is_read ? '' : 'unread' }}">
                                    <div class="notif-panel-body">
                                        @if(!$notif->is_read)
                                            <span class="notif-panel-dot"></span>
                                        @endif
                                        <div class="notif-panel-text">
                                            <div class="notif-panel-item-title">{{ $notif->title }}</div>
                                            <div class="notif-panel-item-msg">
                                                {{ Str::limit($notif->message, 70) }}
                                            </div>
                                            <div class="notif-panel-time">
                                                {{ $notif->created_at->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>
                                    @if(!$notif->is_read)
                                        @php
                                            $readRoute = match(auth()->user()->role) {
                                                'admin'    => route('admin.notifications.read', $notif),
                                                'provider' => route('provider.notifications.read', $notif),
                                                default    => route('customer.notifications.read', $notif),
                                            };
                                        @endphp
                                        <form action="{{ $readRoute }}" method="POST"
                                              style="display:inline;flex-shrink:0;">
                                            @csrf
                                            <button type="submit" class="notif-read-btn"
                                                    title="Mark as read">✓</button>
                                        </form>
                                    @endif
                                </li>
                            @endforeach
                        </ul>

                        <div class="notif-panel-footer">
                            <a href="{{ $navAllRoute }}">View all notifications</a>
                        </div>
                    @else
                        <div class="notif-panel-empty">
                            <span style="font-size:1.5rem;">🔔</span>
                            <p>No notifications yet</p>
                        </div>
                    @endif

                </div>{{-- /notif-panel --}}

            </li>{{-- /notif-dropdown-wrap --}}

            {{-- User display name --}}
            <li class="nav-username" title="{{ auth()->user()->email }}">
                {{ Str::limit(auth()->user()->name, 16) }}
            </li>

            {{-- Logout --}}
            <li>
                <form action="{{ route('logout') }}" method="POST" class="navbar-form">
                    @csrf
                    <button type="submit">Sign Out</button>
                </form>
            </li>
        @endauth

    </ul>
</nav>

@push('scripts')
<script>
(function () {
    var bell  = document.getElementById('notifBell');
    var panel = document.getElementById('notifPanel');

    if (!bell || !panel) return;

    // Toggle
    window.toggleNotifPanel = function (e) {
        e.stopPropagation();
        var open = panel.classList.toggle('open');
        bell.setAttribute('aria-expanded', open ? 'true' : 'false');
    };

    // Close on outside click
    document.addEventListener('click', function (e) {
        if (!document.getElementById('notifWrap').contains(e.target)) {
            panel.classList.remove('open');
            bell.setAttribute('aria-expanded', 'false');
        }
    });

    // Close on Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            panel.classList.remove('open');
            bell.setAttribute('aria-expanded', 'false');
            bell.focus();
        }
    });
})();
</script>
@endpush
