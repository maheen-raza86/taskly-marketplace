@extends('layouts.app')

@section('title', 'Browse Services')

@section('content')

<div class="page-wrap">

    <div class="page-header">
        <h1>Browse Services</h1>
        <span class="sub">{{ $services->total() }} service{{ $services->total() !== 1 ? 's' : '' }} found</span>
    </div>

    {{-- Filter bar --}}
    <form action="{{ route('services.index') }}" method="GET">
        <div class="filter-bar">
            <input
                type="text"
                name="keyword"
                value="{{ request('keyword') }}"
                placeholder="Keyword…"
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
            <select name="price_type">
                <option value="">Any price type</option>
                <option value="fixed"  {{ request('price_type') === 'fixed'  ? 'selected' : '' }}>Fixed</option>
                <option value="hourly" {{ request('price_type') === 'hourly' ? 'selected' : '' }}>Hourly</option>
            </select>
            <input
                type="text"
                name="city"
                value="{{ request('city') }}"
                placeholder="City"
            >
            <button type="submit" class="btn-filter">Search</button>
            @if(request()->hasAny(['keyword','category_id','price_type','city']))
                <a href="{{ route('services.index') }}" class="btn-clear">Clear</a>
            @endif
        </div>
    </form>

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
                                @for($i=1;$i<=5;$i++){{ $i<=$r?'★':'☆' }}@endfor
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
                        View & Book &rarr;
                    </a>
                </div>
            @endforeach
        </div>

        @if($services->hasPages())
            <div class="pagination-wrap">{{ $services->links() }}</div>
        @endif
    @else
        <div class="empty-state">
            <div class="icon">🔍</div>
            <p>No services match your filters.</p>
        </div>
    @endif

</div>

@endsection
