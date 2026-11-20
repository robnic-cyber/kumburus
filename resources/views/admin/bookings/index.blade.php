@extends('layouts.app')

@section('title', 'Manage bookings — Kumburus')

@section('content')
<section class="container section-space admin-shell">
    <div class="dashboard-hero">
        <div>
            <p class="eyebrow">ADMIN CONSOLE</p>
            <h1>Manage bookings</h1>
            <p class="lead">Review every reservation and adjust stay details when plans change.</p>
        </div>
        <div class="dashboard-actions">
            <a class="button button-secondary" href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a class="button" href="{{ route('admin.hotels.index') }}">Manage stays</a>
        </div>
    </div>

    <section class="dashboard-panel">
        @if($bookings->isEmpty())
            <div class="empty-state">
                <h3>No bookings yet</h3>
                <p>Reservations will appear here after a guest completes a booking.</p>
            </div>
        @else
            <div class="booking-table admin-booking-table">
                <div class="booking-table-row booking-table-header">
                    <span>Guest</span>
                    <span>Stay</span>
                    <span>Dates</span>
                    <span>Status</span>
                    <span>Total</span>
                    <span></span>
                </div>
                @foreach($bookings as $booking)
                    <div class="booking-table-row admin-booking-row">
                        <div>
                            <strong>{{ $booking->user->name }}</strong>
                            <span>{{ $booking->user->email }}</span>
                        </div>
                        <div>
                            <strong>{{ $booking->room->hotel->name }}</strong>
                            <span>{{ $booking->room->name }}</span>
                        </div>
                        <div>
                            <strong>{{ $booking->check_in->format('M d, Y') }}</strong>
                            <span>to {{ $booking->check_out->format('M d, Y') }}</span>
                        </div>
                        <span class="status-badge status-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span>
                        <div><strong>₱{{ number_format((float) $booking->total_amount, 0) }}</strong><span>{{ $booking->payment_method_label }}{{ $booking->payment_provider_label ? ' · '.$booking->payment_provider_label : '' }}</span><span>Paid: ₱{{ number_format((float) $booking->payment_amount, 2) }}</span></div>
                        <a class="text-link" href="{{ route('admin.bookings.edit', $booking) }}">Edit</a>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</section>
@endsection
