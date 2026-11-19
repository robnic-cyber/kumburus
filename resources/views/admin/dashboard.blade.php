@extends('layouts.app')

@section('title', 'Admin dashboard — Kumburus')

@section('content')
<section class="container section-space admin-shell">
    <div class="dashboard-hero">
        <div>
            <p class="eyebrow">ADMIN DASHBOARD</p>
            <h1>Welcome back, {{ auth()->user()->name }}</h1>
            <p class="lead">Keep every Kumburus stay polished, available, and ready for your guests.</p>
        </div>
        <div class="dashboard-actions">
            <a class="button button-secondary" href="{{ route('admin.hotels.index') }}">Manage stays</a>
            <a class="button" href="{{ route('admin.bookings.index') }}">View bookings</a>
        </div>
    </div>

    <div class="metric-grid">
        <article class="metric-card metric-card-accent">
            <span>All bookings</span>
            <strong>{{ $stats['total_bookings'] }}</strong>
            <em>Every reservation in the system</em>
        </article>
        <article class="metric-card">
            <span>Confirmed</span>
            <strong>{{ $stats['confirmed_bookings'] }}</strong>
            <em>Ready for guest arrival</em>
        </article>
        <article class="metric-card">
            <span>Pending</span>
            <strong>{{ $stats['pending_bookings'] }}</strong>
            <em>Awaiting final confirmation</em>
        </article>
        <article class="metric-card">
            <span>Booking value</span>
            <strong>₱{{ number_format((float) $stats['revenue'], 0) }}</strong>
            <em>Pending and confirmed stays</em>
        </article>
    </div>

    <div class="dashboard-grid">
        <section class="dashboard-panel">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">RECENT ACTIVITY</p>
                    <h2>Latest bookings</h2>
                </div>
                <a class="text-link" href="{{ route('admin.bookings.index') }}">See all</a>
            </div>

            @if($recentBookings->isEmpty())
                <div class="empty-state">
                    <h3>No bookings yet</h3>
                    <p>New reservations will appear here as guests book a room.</p>
                </div>
            @else
                <div class="booking-table">
                    <div class="booking-table-row booking-table-header">
                        <span>Guest</span>
                        <span>Stay</span>
                        <span>Dates</span>
                        <span>Total</span>
                        <span></span>
                    </div>
                    @foreach($recentBookings as $booking)
                        <div class="booking-table-row">
                            <div>
                                <strong>{{ $booking->user->name }}</strong>
                                <span>{{ $booking->confirmation_code }}</span>
                            </div>
                            <div>
                                <strong>{{ $booking->room->hotel->name }}</strong>
                                <span>{{ $booking->room->name }}</span>
                            </div>
                            <div>
                                <strong>{{ $booking->check_in->format('M d') }} – {{ $booking->check_out->format('M d, Y') }}</strong>
                                <span class="status-badge status-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span>
                            </div>
                            <strong>₱{{ number_format((float) $booking->total_amount, 0) }}</strong>
                            <a class="text-link" href="{{ route('admin.bookings.edit', $booking) }}">Edit</a>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="dashboard-panel">
            <div class="panel-heading">
                <div>
                    <p class="eyebrow">YOUR PORTFOLIO</p>
                    <h2>Stays at a glance</h2>
                </div>
                <a class="text-link" href="{{ route('admin.hotels.index') }}">Manage</a>
            </div>

            <div class="dashboard-list">
                @forelse($hotelSummary as $hotel)
                    <div class="dashboard-list-item">
                        <div>
                            <strong>{{ $hotel->name }}</strong>
                            <span>{{ $hotel->city }}, {{ $hotel->country }}</span>
                        </div>
                        <span>{{ $hotel->rooms_count }} {{ Str::plural('room', $hotel->rooms_count) }}</span>
                    </div>
                @empty
                    <div class="empty-state">
                        <h3>No stays yet</h3>
                        <p>Add your first hotel to build the Kumburus collection.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</section>
@endsection
