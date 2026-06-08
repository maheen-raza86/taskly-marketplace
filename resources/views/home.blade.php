@extends('layouts.app')

@section('title', 'Home')

@push('styles')
<style>
.hero {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 55%, #0f3460 100%);
    color: #fff;
    padding: 4rem 2rem 3.5rem;
    text-align: center;
}
.hero h2 { font-size: 2.1rem; font-weight: 800; margin-bottom: .55rem; }
.hero p  { font-size: 1rem; color: #a5b4fc; margin-bottom: 2rem; }

.hero .search-bar {
    display: flex;
    max-width: 680px;
    margin: 0 auto;
    gap: .5rem;
    flex-wrap: wrap;
}
.hero .search-bar input,
.hero .search-bar select {
    flex: 1 1 140px;
    padding: .65rem .9rem;
    border: none;
    border-radius: 8px;
    font-size: .9rem;
    font-family: inherit;
    background: #fff;
    color: #111;
    outline: none;
}
.hero .search-bar button {
    padding: .65rem 1.4rem;
    background: #6366f1;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: .9rem;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    transition: background .15s;
}
.hero .search-bar button:hover { background: #4f46e5; }

@media(max-width:520px) {
    .hero h2 { font-size: 1.5rem; }
    .hero .search-bar { flex-direction: column; }
    .hero .search-bar input,
    .hero .search-bar select,
    .hero .search-bar button { width: 100%; }
}
</style>
@endpush

@section('content')

{{-- ── Hero ─────────────────────────────────────────────────── --}}
<section class="hero">
    <h2>Find trusted local services</h2>
    <p>Plumbers, electricians, tutors, cleaners and more — book instantly.</p>

    <form action="{{ route('home') }}" method="GET" class="search-bar">
        <input
            type="text"
            name="keyword"
            value="{{ request('keyword') }}"
            placeholder="Search services…"
        >
        <select name="category_id">
            <option value="">All categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}"
                    {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>
        <input
            type="text"
            name="city"
            value="{{ request('city') }}"
            placeholder="City"
        >
        <button type="submit">Search</button>
    </form>
</section>

{{-- ── Listing ──────────────────────────────────────────────── --}}
<div class="page-wrap">

    @if(session('success'))
        <div class="alert-success" style="margin-bottom:1.25rem;">{{ session('success') }}</div>
    @endif

    <div class="page-header">
        <h1>
            @if(request()->hasAny(['keyword','category_id','city']))
                Search results
                <span class="sub">— {{ $services->total() }} found</span>
            @else
                Available services
            @endif
        </h1>
        @if(request()->hasAny(['keyword','category_id','city']))
            <a href="{{ route('home') }}" class="btn-ghost" style="font-size:.8rem;padding:.35rem .8rem;">
                ✕ Clear filters
            </a>
        @endif
    </div>

    @if($services->count())
        <div class="services-grid">
            @foreach($services as $service)
                <div class="svc-card">
                    <span class="cat-tag">{{ $service->category->name }}</span>
                    <h3>{{ $service->title }}</h3>
                    <p class="by">by {{ $service->provider->name }}</p>

                    @if($service->provider->providerProfile && $service->provider->providerProfile->total_reviews > 0)
                        <div class="rating-line">
                            <span class="stars">
                                @php $r = round($service->provider->providerProfile->avg_rating); @endphp
                                @for($i=1;$i<=5;$i++){{ $i<=$r ? '★' : '☆' }}@endfor
                            </span>
                            <span>{{ number_format($service->provider->providerProfile->avg_rating,1) }}
                                ({{ $service->provider->providerProfile->total_reviews }})</span>
                        </div>
                    @endif

                    <div class="price-row">
                        <span class="price">₨{{ number_format($service->price,0) }}</span>
                        <span class="price-type">/ {{ $service->price_type }}</span>
                    </div>

                    @if($service->provider->city)
                        <p class="city-line">📍 {{ $service->provider->city }}</p>
                    @endif

                    <a href="{{ route('services.show', $service) }}" class="btn-view">
                        View Details &rarr;
                    </a>
                </div>
            @endforeach
        </div>

        @if($services->hasPages())
            <div class="pagination-wrap">
                {{ $services->links() }}
            </div>
        @endif
    @else
        <div class="empty-state">
            <div class="icon">🔍</div>
            <p>No services found. Try a different search.</p>
        </div>
    @endif

</div>

@endsection
