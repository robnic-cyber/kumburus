@extends('layouts.app')

@section('title', 'My bookings — Kumburus')

@section('content')
<section class="container section-space page-heading"><p class="eyebrow">YOUR KUMBURUS</p><h1>My bookings</h1><p class="muted">Everything you have planned, in one place.</p></section>
<section class="container booking-list">
    @forelse($bookings as $booking)
        <a class="booking-row" href="{{ route('bookings.show', $booking) }}">
            <div class="booking-date"><strong>{{ $booking->check_in->format('d') }}</strong><span>{{ $booking->check_in->format('M Y') }}</span></div>
            <div class="booking-main"><span class="booking-code">{{ $booking->confirmation_code }}</span><h3>{{ $booking->room->hotel->name }}</h3><p>{{ $booking->room->name }} · {{ $booking->check_in->format('M d') }} — {{ $booking->check_out->format('M d, Y') }}</p></div>
            <div class="booking-status"><span class="status status-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span><strong>₱{{ number_format((float) $booking->total_amount, 2) }}</strong></div>
            <span class="row-arrow">→</span>
        </a>
    @empty
        <div class="empty-state"><h3>Your next stay starts here.</h3><p>Explore our rooms and book a place to land.</p><a class="button" href="{{ route('home') }}">Explore stays</a></div>
    @endforelse
</section>
@endsection
