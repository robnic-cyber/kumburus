@extends('layouts.app')

@section('title', 'Edit booking — Kumburus')

@section('content')
<section class="container section-space narrow-page admin-shell">
    <div class="form-heading">
        <p class="eyebrow">RESERVATION EDITOR</p>
        <h1>Update booking</h1>
        <p class="lead">Change the stay details for <strong>{{ $booking->confirmation_code }}</strong>. The total is recalculated when you save.</p>
    </div>

    @if($errors->any())
        <div class="alert alert-error">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="booking-edit-meta">
        <div>
            <span>Guest</span>
            <strong>{{ $booking->user->name }}</strong>
            <small>{{ $booking->user->email }}</small>
        </div>
        <div>
            <span>Current total</span>
            <strong>₱{{ number_format((float) $booking->total_amount, 0) }}</strong>
            <small>{{ $booking->nights }} {{ Str::plural('night', $booking->nights) }}</small>
        </div>
    </div>

    <form class="admin-form" method="POST" action="{{ route('admin.bookings.update', $booking) }}">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <label class="form-field form-field-wide">
                <span>Room</span>
                <select name="room_id" required>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" @selected(old('room_id', $booking->room_id) == $room->id)>
                            {{ $room->hotel->name }} — {{ $room->name }} · ₱{{ number_format((float) $room->price_per_night, 0) }}/night
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="form-field">
                <span>Check-in</span>
                <input type="date" name="check_in" value="{{ old('check_in', $booking->check_in?->format('Y-m-d')) }}" required>
            </label>

            <label class="form-field">
                <span>Check-out</span>
                <input type="date" name="check_out" value="{{ old('check_out', $booking->check_out?->format('Y-m-d')) }}" required>
            </label>

            <label class="form-field">
                <span>Guests</span>
                <input type="number" name="guests" min="1" max="20" value="{{ old('guests', $booking->guests) }}" required>
            </label>

            <label class="form-field">
                <span>Status</span>
                <select name="status" required>
                    @foreach(['pending', 'confirmed', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $booking->status) === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </label>

            <label class="form-field">
                <span>Payment method</span>
                <select name="payment_method">
                    @foreach(\App\Models\Booking::paymentMethods() as $value => $label)
                        <option value="{{ $value }}" @selected(old('payment_method', $booking->payment_method) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <label class="form-field">
                <span>Payment provider</span>
                <select name="payment_provider">
                    <option value="">Not applicable</option>
                    @foreach(\App\Models\Booking::paymentProviders() as $value => $label)
                        <option value="{{ $value }}" @selected(old('payment_provider', $booking->payment_provider) === $value)>{{ $label }}</option>
                    @endforeach
                    @foreach(\App\Models\Booking::bankProviders() as $value => $label)
                        <option value="{{ $value }}" @selected(old('payment_provider', $booking->payment_provider) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <label class="form-field">
                <span>Payment amount (₱)</span>
                <input type="number" name="payment_amount" min="0.01" step="0.01" value="{{ old('payment_amount', $booking->total_amount) }}" readonly required>
                <small>Automatically calculated from the booking total.</small>
            </label>

            <label class="form-field form-field-wide">
                <span>Special requests</span>
                <textarea name="special_requests" rows="5" placeholder="Add an arrival note, accessibility need, or other request">{{ old('special_requests', $booking->special_requests) }}</textarea>
            </label>
        </div>

        <div class="form-actions">
            <a class="button button-secondary" href="{{ route('admin.bookings.index') }}">Cancel</a>
            <button class="button" type="submit">Save booking changes</button>
        </div>
    </form>
</section>
@endsection
