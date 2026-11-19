<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Hotel;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_bookings' => Booking::count(),
            'confirmed_bookings' => Booking::where('status', 'confirmed')->count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'revenue' => Booking::whereIn('status', ['pending', 'confirmed'])->sum('total_amount'),
        ];

        $recentBookings = Booking::with(['user', 'room.hotel'])
            ->latest()
            ->take(8)
            ->get();

        $hotelSummary = Hotel::withCount('rooms')
            ->orderByDesc('rooms_count')
            ->orderBy('name')
            ->take(6)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentBookings', 'hotelSummary'));
    }
}
