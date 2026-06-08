<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — Taskly Admin</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/customer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/provider.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @stack('styles')
</head>
<body>

@include('layouts.navbar')

<div class="admin-wrap">

    {{-- ── Sidebar ──────────────────────────────────────────── --}}
    <aside class="admin-sidebar">
        @php
            $pendingApprovals = \App\Models\ProviderProfile::where('is_approved', false)->count();
            $unreadNotifs     = auth()->user()->notifications()->where('is_read', false)->count();
            $route            = request()->route()->getName() ?? '';
        @endphp

        <div class="sidebar-section">Main</div>
        <a href="{{ route('admin.dashboard') }}"
           class="{{ $route === 'admin.dashboard' ? 'active' : '' }}">
            <span class="nav-icon">📊</span> Dashboard
        </a>

        <div class="sidebar-section">Users</div>
        <a href="{{ route('admin.providers') }}"
           class="{{ $route === 'admin.providers' ? 'active' : '' }}">
            <span class="nav-icon">🔧</span> Provider Approvals
            @if($pendingApprovals > 0)
                <span class="badge-count">{{ $pendingApprovals }}</span>
            @endif
        </a>

        <div class="sidebar-section">Content</div>
        <a href="{{ route('admin.categories') }}"
           class="{{ $route === 'admin.categories' ? 'active' : '' }}">
            <span class="nav-icon">🏷</span> Categories
        </a>

        <div class="sidebar-section">Bookings</div>
        <a href="{{ route('admin.bookings') }}"
           class="{{ $route === 'admin.bookings' ? 'active' : '' }}">
            <span class="nav-icon">📋</span> All Bookings
        </a>

        <div class="sidebar-section">Account</div>
        <a href="{{ route('admin.notifications') }}"
           class="{{ $route === 'admin.notifications' ? 'active' : '' }}">
            <span class="nav-icon">🔔</span> Notifications
            @if($unreadNotifs > 0)
                <span class="badge-count">{{ $unreadNotifs }}</span>
            @endif
        </a>
    </aside>

    {{-- ── Main content ─────────────────────────────────────── --}}
    <main class="admin-main">

        @if(session('success'))
            <div class="alert-success" style="margin-bottom:1.25rem;">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert-errors" style="margin-bottom:1.25rem;">
                <ul>
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('admin-content')

    </main>

</div>

@stack('scripts')
</body>
</html>
