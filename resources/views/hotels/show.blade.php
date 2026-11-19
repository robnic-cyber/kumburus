@extends('layouts.app')

@section('title', $hotel->name.' — Kumburus')

@section('content')
<section class="container detail-hero"><a class="back-link" href="{{ route('home') }}">← Back to stays</a><div class="detail-image" style="background-image:url('{{ $hotel->image_url ?: 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1600&q=80' }}')"></div><div class="detail-intro"><div><p class="eyebrow">{{ $hotel->city }}, {{ $hotel->country }}</p><h1>{{ $hotel->name }}</h1><p class="muted">{{ $hotel->address }}</p></div><div class="stars stars-large">{{ str_repeat('✦', $hotel->star_rating) }}</div></div><p class="detail-description">{{ $hotel->description }}</p></section>

<section class="container section-space"><div class="section-heading"><div><p class="eyebrow">CHOOSE YOUR ROOM</p><h2>Make it yours</h2></div><span class="muted">Prices shown per night</span></div><div class="room-list">
    @forelse($hotel->rooms as $room)
        <article class="room-card"><div class="room-number">{{ $room->room_number }}</div><div class="room-info"><h3>{{ $room->name }}</h3><p>{{ $room->description }}</p><div class="tag-list">@foreach($room->amenities ?? [] as $amenity)<span>{{ $amenity }}</span>@endforeach</div></div><div class="room-action"><strong>${{ number_format((float) $room->price_per_night, 0) }}</strong><small>per night · up to {{ $room->capacity }} guests</small>@auth<a class="button button-small" href="{{ route('bookings.create', $room) }}">Book this room</a>@else<a class="button button-small" href="{{ route('login') }}">Sign in to book</a>@endauth</div></article>
    @empty
        <div class="empty-state"><h3>No rooms are currently available.</h3></div>
    @endforelse
</div></section>
@endsection
