@extends('layouts.admin')

@section('title', 'All Bookings')

@section('admin-content')

<div class="admin-page-hdr">
    <h1>All Bookings</h1>
    <span class="sub">{{ $bookings->total() }} total</span>
</div>

{{-- ── Status filter ─────────────────────────────────────────── --}}
<form action="{{ route('admin.bookings') }}" method="GET">
    <div class="admin-filter">
        <select name="status" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach(['pending','confirmed','completed','cancelled'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                    {{ ucfirst($s) }}
                </option>
            @endforeach
        </select>

        @if(request('status'))
            <a href="{{ route('admin.bookings') }}" class="btn-clear">✕ Clear</a>
        @endif

        <span style="font-size:.8rem;color:#6b7280;margin-left:auto;">
            Showing {{ $bookings->count() }} of {{ $bookings->total() }}
            @if(request('status')) — filtered by <strong>{{ request('status') }}</strong> @endif
        </span>
    </div>
</form>

{{-- ── Bookings table ────────────────────────────────────────── --}}
<div class="admin-card">

    @if($bookings->count())
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $bk)
                        <tr>
                            <td class="muted">{{ $bk->id }}</td>

                            <td>
                                <div style="font-weight:600;">{{ $bk->customer->name }}</div>
                                <div class="muted">{{ $bk->customer->city ?? '' }}</div>
                            </td>

                            <td>
                                <div style="font-weight:600;">{{ $bk->provider->name }}</div>
                                <div class="muted">{{ $bk->provider->city ?? '' }}</div>
                            </td>

                            <td>
                                {{ $bk->service->title }}
                                <div class="muted">
                                    ₨{{ number_format($bk->service->price, 0) }} / {{ $bk->service->price_type }}
                                </div>
                            </td>

                            <td style="white-space:nowrap;">
                                {{ $bk->booking_date->format('d M Y') }}
                            </td>

                            <td style="white-space:nowrap;" class="muted">
                                {{ \Carbon\Carbon::parse($bk->time_slot)->format('g:i A') }}
                            </td>

                            <td>
                                <span class="badge badge-{{ $bk->status }}">
                                    {{ ucfirst($bk->status) }}
                                </span>
                            </td>

                            <td>
                                <div class="btn-group">
                                    {{-- View customer bookings link --}}
                                    @if($bk->status !== 'cancelled' && $bk->status !== 'completed')
                                        <form action="{{ route('admin.bookings.updateStatus', $bk) }}"
                                              method="POST" style="display:contents;">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="cancelled">
                                            <button
                                                type="submit"
                                                class="btn-xs btn-cancel"
                                                onclick="return confirm('Cancel booking #{{ $bk->id }} for {{ addslashes($bk->customer->name) }}? The customer will be notified.')">
                                                Cancel
                                            </button>
                                        </form>
                                    @else
                                        <span class="muted" style="font-size:.75rem;">—</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($bookings->hasPages())
            <div class="admin-pagination">{{ $bookings->links() }}</div>
        @endif

    @else
        <div class="admin-empty">
            <div class="icon">📋</div>
            <p>
                @if(request('status'))
                    No {{ request('status') }} bookings found.
                @else
                    No bookings yet.
                @endif
            </p>
        </div>
    @endif

</div>

@endsection
