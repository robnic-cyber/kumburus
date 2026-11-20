@extends('layouts.app')

@section('title', 'Edit booking '.$booking->confirmation_code.' — Kumburus')

@section('content')
<section class="container booking-shell">
    <div class="booking-form-wrap">
        <a class="back-link" href="{{ route('bookings.show', $booking) }}">← Back to booking details</a>
        <p class="eyebrow">UPDATE YOUR STAY</p>
        <h1>Plans changed?</h1>
        <p class="muted">Adjust your dates, room, guests, or special requests. Your new total will be calculated when you save.</p>

        @if($errors->any())
            <div class="alert alert-error">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('bookings.update', $booking) }}" class="stack-form booking-form">
            @csrf
            @method('PUT')

            <label>Room
                <select name="room_id" required>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" @selected(old('room_id', $booking->room_id) == $room->id)>
                            {{ $room->hotel->name }} — {{ $room->name }} · ₱{{ number_format((float) $room->price_per_night, 0) }}/night
                        </option>
                    @endforeach
                </select>
            </label>

            <div class="form-grid">
                <label>Check in<input type="date" name="check_in" value="{{ old('check_in', $booking->check_in->format('Y-m-d')) }}" min="{{ now()->format('Y-m-d') }}" required></label>
                <label>Check out<input type="date" name="check_out" value="{{ old('check_out', $booking->check_out->format('Y-m-d')) }}" min="{{ now()->format('Y-m-d') }}" required></label>
            </div>

            <label>Guests<input type="number" name="guests" value="{{ old('guests', $booking->guests) }}" min="1" max="20" required><small>The room capacity is checked when you save.</small></label>
            <label>Payment method
                <select name="payment_method" required>
                    @foreach(\App\Models\Booking::paymentMethods() as $value => $label)
                        <option value="{{ $value }}" @selected(old('payment_method', $booking->payment_method) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label>Special requests <span class="muted">(optional)</span><textarea name="special_requests" rows="4" placeholder="Anything that would make your stay more comfortable?">{{ old('special_requests', $booking->special_requests) }}</textarea></label>

            <div class="edit-form-actions">
                <a class="button button-secondary" href="{{ route('bookings.show', $booking) }}">Keep current booking</a>
                <button class="button" type="submit">Save booking changes <span>→</span></button>
            </div>
        </form>
    </div>

    <aside class="booking-summary">
        <p class="eyebrow">CURRENT RESERVATION</p>
        <div class="summary-image" style="background-image:url('{{ $booking->room->hotel->image_url ?: 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=900&q=80' }}')"></div>
        <h2>{{ $booking->room->hotel->name }}</h2>
        <p>{{ $booking->room->name }} · {{ $booking->confirmation_code }}</p>
        <div class="summary-price"><strong>₱{{ number_format((float) $booking->total_amount, 0) }}</strong><span>current total</span></div>
        <div class="tag-list"><span>{{ $booking->nights }} {{ Str::plural('night', $booking->nights) }}</span><span>{{ $booking->guests }} {{ Str::plural('guest', $booking->guests) }}</span></div>
    </aside>
</section>
@endsection
