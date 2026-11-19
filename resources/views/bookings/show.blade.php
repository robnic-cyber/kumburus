@extends('layouts.app')

@section('title', 'Booking '.$booking->confirmation_code.' — Kumburus')

@section('content')
<section class="container section-space confirmation-page">
    <div class="confirmation-mark">✓</div>
    <p class="eyebrow">BOOKING CONFIRMED</p>
    <h1>You’re all set.</h1>
    <p class="lead">A confirmation has been sent to <strong>{{ $booking->user->email }}</strong>.</p>

    <div class="confirmation-card">
        <div class="confirmation-top">
            <div>
                <span class="booking-code">{{ $booking->confirmation_code }}</span>
                <h2>{{ $booking->room->hotel->name }}</h2>
                <p>{{ $booking->room->name }}</p>
            </div>
            <span class="status status-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span>
        </div>

        <div class="detail-grid">
            <div><span>Check in</span><strong>{{ $booking->check_in->format('D, M d, Y') }}</strong></div>
            <div><span>Check out</span><strong>{{ $booking->check_out->format('D, M d, Y') }}</strong></div>
            <div><span>Guests</span><strong>{{ $booking->guests }}</strong></div>
            <div><span>Total</span><strong>₱{{ number_format((float) $booking->total_amount, 2) }}</strong></div>
        </div>

        @if($booking->special_requests)
            <div class="request-note">
                <span>Special requests</span>
                <p>{{ $booking->special_requests }}</p>
            </div>
        @endif

        @if($booking->status === 'confirmed' && $booking->check_in->isFuture())
            <form method="POST" action="{{ route('bookings.cancel', $booking) }}" class="cancel-form">
                @csrf
                @method('PATCH')
                <button class="link-button danger" type="submit">Cancel this booking</button>
            </form>
        @endif
    </div>

    <a class="button button-outline" href="{{ route('bookings.index') }}">View all bookings</a>
</section>
@endsection
