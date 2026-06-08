@extends('layouts.app')

@section('title', 'My Bookings')

@section('content')

<div class="page-wrap">

    <div class="page-header">
        <h1>
            @if(auth()->user()->role === 'customer')   My Bookings
            @elseif(auth()->user()->role === 'provider') Incoming Requests
            @else All Bookings
            @endif
        </h1>
        <span class="sub">{{ $bookings->total() }} total</span>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert-success" style="margin-bottom:1.25rem;">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert-errors" style="margin-bottom:1.25rem;">
            <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    @if($bookings->count())
        @if(auth()->user()->role === 'customer')
            {{-- ─────────────────────────────────────────────
                 CUSTOMER VIEW — cards with inline review form
            ───────────────────────────────────────────── --}}
            @foreach($bookings as $booking)
                <div class="card" style="margin-bottom:1.1rem;">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:.5rem;margin-bottom:.65rem;">
                        <div>
                            <div style="font-size:1rem;font-weight:700;color:#1a1a2e;margin-bottom:.2rem;">
                                {{ $booking->service->title }}
                            </div>
                            <div style="font-size:.82rem;color:#6b7280;">
                                Provider: <strong>{{ $booking->provider->name }}</strong>
                                @if($booking->provider->city) &nbsp;·&nbsp; 📍{{ $booking->provider->city }} @endif
                            </div>
                        </div>
                        <span class="badge badge-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span>
                    </div>

                    <div style="display:flex;flex-wrap:wrap;gap:1.25rem;font-size:.83rem;color:#374151;margin-bottom:.5rem;">
                        <span>📅 {{ $booking->booking_date->format('D, d M Y') }}</span>
                        <span>⏰ {{ \Carbon\Carbon::parse($booking->time_slot)->format('g:i A') }}</span>
                        @if($booking->notes)
                            <span style="color:#6b7280;">💬 {{ Str::limit($booking->notes, 60) }}</span>
                        @endif
                    </div>

                    {{-- Booking ID --}}
                    <div style="font-size:.72rem;color:#9ca3af;">Booking #{{ $booking->id }}</div>

                    {{-- Review section (completed + no review yet) --}}
                    @if($booking->status === 'completed')
                        @if($booking->review)
                            {{-- Already reviewed --}}
                            <div style="margin-top:.75rem;padding:.65rem .9rem;background:#f0fdf4;border-radius:8px;font-size:.83rem;color:#065f46;">
                                <span class="stars">
                                    @for($i=1;$i<=5;$i++){{ $i<=$booking->review->rating?'★':'☆' }}@endfor
                                </span>
                                <strong> Your review</strong>
                                @if($booking->review->comment)
                                    — {{ $booking->review->comment }}
                                @endif
                            </div>
                        @else
                            {{-- Inline review form --}}
                            <div class="review-inline">
                                <h4>Leave a Review</h4>
                                <form action="{{ route('customer.reviews.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="booking_id" value="{{ $booking->id }}">

                                    {{-- Star rating --}}
                                    <div class="star-group" id="stars-{{ $booking->id }}">
                                        @for($i=5;$i>=1;$i--)
                                            <input
                                                type="radio"
                                                id="star{{ $i }}-{{ $booking->id }}"
                                                name="rating"
                                                value="{{ $i }}"
                                                {{ old('rating') == $i ? 'checked' : '' }}
                                                required
                                            >
                                            <label for="star{{ $i }}-{{ $booking->id }}" title="{{ $i }} star{{ $i>1?'s':'' }}">★</label>
                                        @endfor
                                    </div>

                                    <textarea
                                        name="comment"
                                        placeholder="Share your experience (optional)…"
                                    >{{ old('comment') }}</textarea>

                                    <button type="submit" class="btn-submit-review">Submit Review</button>
                                </form>
                            </div>
                        @endif
                    @endif

                </div>
            @endforeach

        @elseif(auth()->user()->role === 'provider')
            {{-- ─────────────────────────────────────────────
                 PROVIDER VIEW — table with action buttons
            ───────────────────────────────────────────── --}}
            <div class="card">
                <div class="bookings-table-wrap">
                    <table class="bookings">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Service</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bookings as $booking)
                            <tr>
                                <td>{{ $booking->customer->name }}</td>
                                <td>{{ $booking->service->title }}</td>
                                <td>{{ $booking->booking_date->format('d M Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($booking->time_slot)->format('g:i A') }}</td>
                                <td><span class="badge badge-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span></td>
                                <td>
                                    @if($booking->status === 'pending')
                                        <form action="{{ route('provider.bookings.updateStatus', $booking) }}" method="POST" style="display:inline;">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="confirmed">
                                            <button type="submit" class="btn-primary" style="padding:.3rem .7rem;font-size:.78rem;">Confirm</button>
                                        </form>
                                        <form action="{{ route('provider.bookings.updateStatus', $booking) }}" method="POST" style="display:inline;margin-left:.35rem;">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="btn-ghost" style="padding:.3rem .7rem;font-size:.78rem;color:#b91c1c;">Decline</button>
                                        </form>
                                    @elseif($booking->status === 'confirmed')
                                        <form action="{{ route('provider.bookings.complete', $booking) }}" method="POST" style="display:inline;">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn-ghost" style="padding:.3rem .7rem;font-size:.78rem;">Mark Complete</button>
                                        </form>
                                    @else
                                        <span style="font-size:.78rem;color:#9ca3af;">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        @else
            {{-- ─────────────────────────────────────────────
                 ADMIN VIEW — full table, no actions
            ───────────────────────────────────────────── --}}
            <div class="card">
                <div class="bookings-table-wrap">
                    <table class="bookings">
                        <thead>
                            <tr>
                                <th>#</th><th>Customer</th><th>Provider</th>
                                <th>Service</th><th>Date</th><th>Time</th><th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bookings as $booking)
                            <tr>
                                <td>{{ $booking->id }}</td>
                                <td>{{ $booking->customer->name }}</td>
                                <td>{{ $booking->provider->name }}</td>
                                <td>{{ $booking->service->title }}</td>
                                <td>{{ $booking->booking_date->format('d M Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($booking->time_slot)->format('g:i A') }}</td>
                                <td><span class="badge badge-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Pagination --}}
        @if($bookings->hasPages())
            <div class="pagination-wrap">{{ $bookings->links() }}</div>
        @endif

    @else
        <div class="empty-state">
            <div class="icon">📋</div>
            <p>
                @if(auth()->user()->role === 'customer')
                    You haven't made any bookings yet.
                    <a href="{{ route('services.index') }}" style="color:#4f46e5;">Browse services &rarr;</a>
                @else
                    No bookings yet.
                @endif
            </p>
        </div>
    @endif

</div>

@endsection
