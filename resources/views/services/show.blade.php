@extends('layouts.app')

@section('title', $service->title)

@section('content')

<div class="page-wrap">

    {{-- Breadcrumb --}}
    <nav style="font-size:.8rem;color:#9ca3af;margin-bottom:1.25rem;">
        <a href="{{ route('home') }}" style="color:#6366f1;text-decoration:none;">Home</a>
        <span style="margin:0 .4rem;">/</span>
        <a href="{{ route('services.index') }}" style="color:#6366f1;text-decoration:none;">Services</a>
        <span style="margin:0 .4rem;">/</span>
        {{ $service->title }}
    </nav>

    {{-- Flash / errors --}}
    @if(session('success'))
        <div class="alert-success" style="margin-bottom:1.25rem;">{{ session('success') }}</div>
    @endif
    @if($errors->has('booking'))
        <div class="alert-errors" style="margin-bottom:1.25rem;">{{ $errors->first('booking') }}</div>
    @endif

    {{-- Load availability (controller doesn't eager-load it) --}}
    @php
        $availability = \App\Models\Availability::where('provider_id', $service->provider_id)
            ->orderByRaw("FIELD(day_of_week,'monday','tuesday','wednesday','thursday','friday','saturday','sunday')")
            ->get();
    @endphp

    <div class="detail-grid">

        {{-- ────────────────────────────────────────────────────────
             LEFT — service info
        ──────────────────────────────────────────────────────── --}}
        <div class="detail-main">

            <h1 class="svc-title">{{ $service->title }}</h1>

            <div class="svc-meta">
                <span class="tag tag-cat">{{ $service->category->name }}</span>
                <span class="tag tag-type">{{ ucfirst($service->price_type) }}</span>
                @if($service->provider->city)
                    <span style="font-size:.8rem;color:#6b7280;">📍 {{ $service->provider->city }}</span>
                @endif
            </div>

            <p class="svc-desc">{{ $service->description }}</p>

            {{-- Provider info --}}
            <div class="provider-box">
                <h3>About the provider</h3>
                <p class="pname">
                    <a href="{{ route('provider.profile', $service->provider) }}"
                       style="color:inherit;text-decoration:none;border-bottom:1px dotted #9ca3af;">
                        {{ $service->provider->name }}
                    </a>
                </p>

                @if($service->provider->providerProfile)
                    @php $pp = $service->provider->providerProfile; @endphp
                    @if($pp->total_reviews > 0)
                        <div class="prating">
                            <span class="stars">
                                @php $r = round($pp->avg_rating); @endphp
                                @for($i=1;$i<=5;$i++){{ $i<=$r?'★':'☆' }}@endfor
                            </span>
                            <span>
                                {{ number_format($pp->avg_rating,1) }} / 5
                                ({{ $pp->total_reviews }} review{{ $pp->total_reviews !== 1 ? 's' : '' }})
                            </span>
                        </div>
                    @endif
                    @if($pp->bio)
                        <p class="pbio">{{ $pp->bio }}</p>
                    @endif
                    @if($pp->experience_years > 0)
                        <p class="pexp">
                            {{ $pp->experience_years }}
                            year{{ $pp->experience_years !== 1 ? 's' : '' }} of experience
                        </p>
                    @endif
                @endif
            </div>

            {{-- Availability days --}}
            @if($availability->count())
                <div class="avail-wrap">
                    <h4>Available days</h4>
                    <div class="day-pills">
                        @foreach($availability as $slot)
                            <span class="day-pill">
                                {{ ucfirst($slot->day_of_week) }}
                                <span class="time">
                                    {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }}
                                    – {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                                </span>
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Reviews --}}
            @if($reviews->count())
                <div class="reviews-section">
                    <h2>Reviews ({{ $reviews->count() }})</h2>
                    @foreach($reviews as $review)
                        <div class="review-item">
                            <div class="review-meta">
                                <span class="review-author">{{ $review->customer->name }}</span>
                                <span class="stars" style="font-size:.9rem;">
                                    @for($i=1;$i<=5;$i++){{ $i<=$review->rating?'★':'☆' }}@endfor
                                </span>
                                <span class="review-date">{{ $review->created_at->diffForHumans() }}</span>
                            </div>
                            @if($review->comment)
                                <p class="review-text">{{ $review->comment }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

        </div>{{-- /detail-main --}}

        {{-- ────────────────────────────────────────────────────────
             RIGHT — booking sidebar
        ──────────────────────────────────────────────────────── --}}
        <aside>
            <div class="booking-box">

                <div class="price-hero">
                    <div class="amount">₨{{ number_format($service->price, 0) }}</div>
                    <div class="per">per {{ $service->price_type }}</div>
                </div>

                @auth
                    @if(auth()->user()->role === 'customer')

                        <form action="{{ route('customer.bookings.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="service_id" value="{{ $service->id }}">

                            <label class="form-label" for="booking_date">Date</label>
                            <input
                                type="date"
                                id="booking_date"
                                name="booking_date"
                                min="{{ date('Y-m-d') }}"
                                value="{{ old('booking_date') }}"
                                class="{{ $errors->has('booking_date') ? 'is-invalid' : '' }}"
                                required
                            >
                            @error('booking_date')
                                <span class="field-error">{{ $message }}</span>
                            @enderror

                            <label class="form-label" for="time_slot">Time slot</label>
                            <input
                                type="time"
                                id="time_slot"
                                name="time_slot"
                                value="{{ old('time_slot') }}"
                                class="{{ $errors->has('time_slot') ? 'is-invalid' : '' }}"
                                required
                            >
                            @error('time_slot')
                                <span class="field-error">{{ $message }}</span>
                            @enderror

                            <label class="form-label" for="notes">
                                Notes
                                <span style="font-weight:400;text-transform:none;font-size:.73rem;color:#9ca3af;">
                                    (optional)
                                </span>
                            </label>
                            <textarea
                                id="notes"
                                name="notes"
                                placeholder="Any special instructions for the provider…"
                                class="{{ $errors->has('notes') ? 'is-invalid' : '' }}"
                            >{{ old('notes') }}</textarea>
                            @error('notes')
                                <span class="field-error">{{ $message }}</span>
                            @enderror

                            <button type="submit" class="btn-book">Request Booking</button>
                        </form>

                        @if($availability->count())
                            <p style="font-size:.72rem;color:#9ca3af;text-align:center;margin-top:.75rem;">
                                ✓ Available:
                                @foreach($availability as $slot)
                                    {{ ucfirst(substr($slot->day_of_week,0,3)) }}{{ !$loop->last ? ', ' : '' }}
                                @endforeach
                            </p>
                        @endif

                    @else
                        <div class="login-to-book">
                            <p>Only customers can make bookings.</p>
                        </div>
                    @endif
                @else
                    <div class="login-to-book">
                        <p style="margin-bottom:.75rem;">Sign in to book this service</p>
                        <a href="{{ route('login') }}" class="btn-book">Sign In to Book</a>
                    </div>
                @endauth

            </div>
        </aside>

    </div>{{-- /detail-grid --}}

</div>

@endsection
