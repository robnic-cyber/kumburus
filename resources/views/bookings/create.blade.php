@extends('layouts.app')

@section('title', 'Book '.$room->name.' — Kumburus')

@section('content')
<section class="container booking-shell">
    <div class="booking-form-wrap">
        <a class="back-link" href="{{ route('hotels.show', $room->hotel) }}">← Back to {{ $room->hotel->name }}</a>
        <p class="eyebrow">RESERVE YOUR ROOM</p>
        <h1>Almost there.</h1>
        <p class="muted">Choose your dates and we’ll take care of the rest.</p>
        <form method="POST" action="{{ route('bookings.store') }}" class="stack-form booking-form">
            @csrf
            <input type="hidden" name="room_id" value="{{ $room->id }}">
            <div class="form-grid">
                <label>Check in<input type="date" name="check_in" value="{{ old('check_in', now()->addDay()->format('Y-m-d')) }}" min="{{ now()->format('Y-m-d') }}" required></label>
                <label>Check out<input type="date" name="check_out" value="{{ old('check_out', now()->addDays(2)->format('Y-m-d')) }}" min="{{ now()->addDay()->format('Y-m-d') }}" required></label>
            </div>
            <label>Guests<input type="number" name="guests" value="{{ old('guests', 1) }}" min="1" max="{{ $room->capacity }}" required><small>Up to {{ $room->capacity }} guests for this room.</small></label>
            <label>Payment method
                <select name="payment_method" required>
                    @foreach(\App\Models\Booking::paymentMethods() as $value => $label)
                        <option value="{{ $value }}" @selected(old('payment_method', 'pay_at_hotel') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <small>Your selected payment method is saved with the booking. No payment is charged yet.</small>
            </label>
            <label>Special requests <span class="muted">(optional)</span><textarea name="special_requests" rows="4" placeholder="Anything that would make your stay more comfortable?">{{ old('special_requests') }}</textarea></label>
            <button class="button button-full" type="submit">Confirm booking <span>→</span></button>
        </form>
    </div>
    <aside class="booking-summary">
        <p class="eyebrow">YOUR ROOM</p>
        <div class="summary-image" style="background-image:url('{{ $room->hotel->image_url ?: 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=900&q=80' }}')"></div>
        <h2>{{ $room->name }}</h2>
        <p>{{ $room->hotel->name }} · {{ $room->hotel->city }}</p>
        <div class="summary-price"><strong>₱{{ number_format((float) $room->price_per_night, 0) }}</strong><span>per night</span></div>
        <div class="tag-list">@foreach($room->amenities ?? [] as $amenity)<span>{{ $amenity }}</span>@endforeach</div>
    </aside>
</section>
@endsection
