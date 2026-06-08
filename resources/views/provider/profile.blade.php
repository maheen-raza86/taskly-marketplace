@extends('layouts.app')

@section('title', $provider->name)

@section('content')

<div class="page-wrap">

    {{-- Breadcrumb --}}
    <nav style="font-size:.8rem;color:#9ca3af;margin-bottom:1.25rem;">
        <a href="{{ route('home') }}" style="color:#6366f1;text-decoration:none;">Home</a>
        <span style="margin:0 .4rem;">/</span>
        <span>{{ $provider->name }}</span>
    </nav>

    {{-- Hero banner --}}
    <div class="card" style="margin-bottom:1.5rem;padding:2rem;">
        <div style="display:flex;align-items:flex-start;gap:1.5rem;flex-wrap:wrap;">

            {{-- Avatar initials --}}
            <div style="width:64px;height:64px;border-radius:50%;background:#eef2ff;display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:800;color:#4f46e5;flex-shrink:0;">
                {{ strtoupper(substr($provider->name, 0, 1)) }}
            </div>

            <div style="flex:1;min-width:0;">
                <h1 style="font-size:1.5rem;font-weight:800;color:#1a1a2e;margin-bottom:.3rem;">
                    {{ $provider->name }}
                </h1>

                <div style="display:flex;flex-wrap:wrap;gap:.65rem;align-items:center;font-size:.83rem;color:#6b7280;margin-bottom:.75rem;">
                    @if($provider->city)
                        <span>📍 {{ $provider->city }}</span>
                    @endif
                    @if($provider->phone)
                        <span>📞 {{ $provider->phone }}</span>
                    @endif
                    @if($provider->providerProfile)
                        @php $pp = $provider->providerProfile; @endphp
                        @if($pp->experience_years > 0)
                            <span>🏅 {{ $pp->experience_years }} yr{{ $pp->experience_years !== 1 ? 's' : '' }} experience</span>
                        @endif
                    @endif
                </div>

                {{-- Rating --}}
                @if(isset($pp) && $pp->total_reviews > 0)
                    <div style="display:flex;align-items:center;gap:.5rem;">
                        <span style="color:#f59e0b;font-size:1.1rem;letter-spacing:.5px;">
                            @php $rnd = round($pp->avg_rating); @endphp
                            @for($i=1;$i<=5;$i++){{ $i<=$rnd ? '★' : '☆' }}@endfor
                        </span>
                        <span style="font-size:.875rem;color:#374151;font-weight:600;">
                            {{ number_format($pp->avg_rating, 1) }}
                        </span>
                        <span style="font-size:.8rem;color:#9ca3af;">
                            ({{ $pp->total_reviews }} review{{ $pp->total_reviews !== 1 ? 's' : '' }})
                        </span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Bio --}}
        @if(isset($pp) && $pp->bio)
            <p style="margin-top:1.1rem;font-size:.9rem;color:#374151;line-height:1.7;border-top:1px solid #f3f4f6;padding-top:1rem;">
                {{ $pp->bio }}
            </p>
        @endif
    </div>

    <div style="display:grid;grid-template-columns:1fr 300px;gap:1.5rem;align-items:start;">
        <div>

            {{-- Services --}}
            @if($provider->services->count())
                <div class="card" style="margin-bottom:1.5rem;">
                    <div class="card-title">Services Offered ({{ $provider->services->count() }})</div>
                    <div class="svc-list">
                        @foreach($provider->services as $svc)
                            <div class="svc-item">
                                <div class="svc-info">
                                    <div class="svc-name">{{ $svc->title }}</div>
                                    <div class="svc-meta">
                                        <span>{{ $svc->category->name }}</span>
                                        <span>₨{{ number_format($svc->price, 0) }} / {{ $svc->price_type }}</span>
                                    </div>
                                    @if($svc->description)
                                        <p style="font-size:.78rem;color:#6b7280;margin-top:.3rem;line-height:1.4;">
                                            {{ Str::limit($svc->description, 120) }}
                                        </p>
                                    @endif
                                </div>
                                <a href="{{ route('services.show', $svc) }}" class="btn-sm btn-edit"
                                   style="white-space:nowrap;">Book →</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Reviews --}}
            @if($reviews->count())
                <div class="card">
                    <div class="card-title">Reviews</div>
                    @foreach($reviews as $review)
                        <div class="review-item">
                            <div class="review-meta">
                                <span class="review-author">{{ $review->customer->name }}</span>
                                <span class="stars" style="font-size:.9rem;color:#f59e0b;">
                                    @for($i=1;$i<=5;$i++){{ $i<=$review->rating?'★':'☆' }}@endfor
                                </span>
                                <span class="review-date" style="color:#9ca3af;font-size:.75rem;">
                                    {{ $review->created_at->diffForHumans() }}
                                </span>
                            </div>
                            @if($review->comment)
                                <p class="review-text" style="font-size:.875rem;color:#374151;line-height:1.55;">
                                    {{ $review->comment }}
                                </p>
                            @endif
                        </div>
                    @endforeach

                    @if($reviews->hasPages())
                        <div class="pagination-wrap" style="margin-top:1rem;">
                            {{ $reviews->links() }}
                        </div>
                    @endif
                </div>
            @else
                <div style="text-align:center;padding:2rem;color:#9ca3af;font-size:.875rem;">
                    No reviews yet.
                </div>
            @endif

        </div>

        {{-- Sidebar: availability --}}
        <div>
            @php
                $providerAvail = \App\Models\Availability::where('provider_id', $provider->id)
                    ->orderByRaw("FIELD(day_of_week,'monday','tuesday','wednesday','thursday','friday','saturday','sunday')")
                    ->get();
            @endphp

            @if($providerAvail->count())
                <div class="card">
                    <div class="card-title">Available Days</div>
                    <div class="avail-pills" style="flex-direction:column;gap:.4rem;">
                        @foreach($providerAvail as $slot)
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:.4rem 0;border-bottom:1px solid #f3f4f6;">
                                <span style="font-size:.875rem;font-weight:600;color:#1a1a2e;">
                                    {{ ucfirst($slot->day_of_week) }}
                                </span>
                                <span style="font-size:.8rem;color:#6b7280;">
                                    {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }}
                                    – {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>

</div>

@endsection
