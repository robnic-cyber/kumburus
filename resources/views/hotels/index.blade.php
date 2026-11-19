@extends('layouts.app')

@section('title', 'Kumburus — Find your next stay')

@section('content')
<section class="hero">
    <div class="container hero-grid">
        <div>
            <p class="eyebrow">YOUR NEXT GOOD MORNING</p>
            <h1>Stay somewhere<br><em>worth remembering.</em></h1>
            <p class="hero-copy">Handpicked rooms, flexible booking, and one calm place to keep every detail of your stay.</p>
            <form class="search-bar" method="GET" action="{{ route('home') }}">
                <label><span>Where</span><input type="text" name="city" value="{{ request('city') }}" placeholder="City or destination"></label>
                <button class="button" type="submit">Explore stays <span>→</span></button>
            </form>
        </div>
        <div class="hero-art"><div class="hero-circle"></div><div class="hero-card"><span>✦</span><strong>Thoughtful stays</strong><small>made for slow mornings</small></div></div>
    </div>
</section>

<section class="container section-space">
    <div class="section-heading"><div><p class="eyebrow">CURATED FOR YOU</p><h2>Places with a point of view</h2></div><span class="muted">{{ $hotels->count() }} available stays</span></div>
    <div class="hotel-grid">
        @forelse($hotels as $hotel)
            <article class="hotel-card">
                <a href="{{ route('hotels.show', $hotel) }}" class="hotel-image" style="background-image:url('{{ $hotel->image_url ?: 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80' }}')"><span class="image-label">{{ $hotel->city }}, {{ $hotel->country }}</span></a>
                <div class="hotel-card-body"><div class="stars">{{ str_repeat('✦', $hotel->star_rating) }}</div><h3><a href="{{ route('hotels.show', $hotel) }}">{{ $hotel->name }}</a></h3><p>{{ Illuminate\Support\Str::limit($hotel->description, 110) }}</p><div class="card-meta"><span>{{ $hotel->rooms_count }} rooms</span><a href="{{ route('hotels.show', $hotel) }}">View rooms →</a></div></div>
            </article>
        @empty
            <div class="empty-state"><h3>No stays found</h3><p>Try another city or clear your search.</p><a class="button button-outline" href="{{ route('home') }}">Show all stays</a></div>
        @endforelse
    </div>
</section>

<section class="promise-band"><div class="container promise-grid"><div><p class="eyebrow">THE KUMBURUS PROMISE</p><h2>Booking should feel<br><em>as good as arriving.</em></h2></div><div class="promise-list"><div><span>01</span><p><strong>Clear pricing</strong><br>See your total before you confirm.</p></div><div><span>02</span><p><strong>Protected dates</strong><br>Every booking is checked in one transaction.</p></div><div><span>03</span><p><strong>Inbox ready</strong><br>A confirmation lands in your email.</p></div></div></div></section>
@endsection
