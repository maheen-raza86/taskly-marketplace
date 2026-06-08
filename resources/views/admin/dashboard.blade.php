@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('admin-content')

<div class="admin-page-hdr">
    <h1>Dashboard</h1>
    <span style="font-size:.8rem;color:#9ca3af;">{{ now()->format('l, d F Y') }}</span>
</div>

{{-- ── Stats ─────────────────────────────────────────────────── --}}
<div class="admin-stats">

    <div class="admin-stat c-indigo">
        <span class="stat-icon">👥</span>
        <span class="stat-val">{{ $stats['total_users'] }}</span>
        <span class="stat-lbl">Total users</span>
    </div>

    <div class="admin-stat c-blue">
        <span class="stat-icon">🔧</span>
        <span class="stat-val">{{ $stats['total_providers'] }}</span>
        <span class="stat-lbl">Providers</span>
        <a href="{{ route('admin.providers') }}" class="stat-link">View pending →</a>
    </div>

    <div class="admin-stat c-teal">
        <span class="stat-icon">🛒</span>
        <span class="stat-val">{{ $stats['total_customers'] }}</span>
        <span class="stat-lbl">Customers</span>
    </div>

    <div class="admin-stat c-amber">
        <span class="stat-icon">⏳</span>
        <span class="stat-val">{{ $stats['pending_providers'] }}</span>
        <span class="stat-lbl">Pending approvals</span>
        @if($stats['pending_providers'] > 0)
            <a href="{{ route('admin.providers') }}" class="stat-link">Review now →</a>
        @endif
    </div>

    <div class="admin-stat c-purple">
        <span class="stat-icon">🛠</span>
        <span class="stat-val">{{ $stats['total_services'] }}</span>
        <span class="stat-lbl">Total services</span>
    </div>

    <div class="admin-stat c-green">
        <span class="stat-icon">📋</span>
        <span class="stat-val">{{ $stats['total_bookings'] }}</span>
        <span class="stat-lbl">Total bookings</span>
        <a href="{{ route('admin.bookings') }}" class="stat-link">View all →</a>
    </div>

    <div class="admin-stat c-rose">
        <span class="stat-icon">📌</span>
        <span class="stat-val">{{ $stats['pending_bookings'] }}</span>
        <span class="stat-lbl">Pending bookings</span>
    </div>

    <div class="admin-stat c-green">
        <span class="stat-icon">✅</span>
        <span class="stat-val">{{ $stats['completed_bookings'] }}</span>
        <span class="stat-lbl">Completed bookings</span>
    </div>

</div>

{{-- ── Recent bookings ───────────────────────────────────────── --}}
<div class="admin-card">
    <div class="admin-card-title">
        Recent Bookings
        <a href="{{ route('admin.bookings') }}"
           style="font-size:.8rem;font-weight:500;color:#6366f1;text-decoration:none;">
            View all →
        </a>
    </div>

    @if($recentBookings->count())
        <div class="tbl-scroll">
            <table class="admin-tbl">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Provider</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentBookings as $bk)
                        <tr>
                            <td class="muted">{{ $bk->id }}</td>
                            <td style="font-weight:600;">{{ $bk->customer->name }}</td>
                            <td>{{ $bk->provider->name }}</td>
                            <td>{{ $bk->service->title }}</td>
                            <td class="muted" style="white-space:nowrap;">
                                {{ $bk->booking_date->format('d M Y') }}
                            </td>
                            <td class="muted" style="white-space:nowrap;">
                                {{ \Carbon\Carbon::parse($bk->time_slot)->format('g:i A') }}
                            </td>
                            <td>
                                <span class="badge badge-{{ $bk->status }}">
                                    {{ ucfirst($bk->status) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="admin-empty">
            <div class="icon">📋</div>
            <p>No bookings yet.</p>
        </div>
    @endif
</div>

@endsection
