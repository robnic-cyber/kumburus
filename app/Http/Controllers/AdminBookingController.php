<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminBookingController extends Controller
{
    public function index(): View
    {
        $bookings = Booking::with(['user', 'room.hotel'])
            ->latest()
            ->get();

        return view('admin.bookings.index', compact('bookings'));
    }

    public function edit(Booking $booking): View
    {
        $booking->load(['user', 'room.hotel']);

        $rooms = Room::with('hotel')
            ->where(function ($query) use ($booking): void {
                $query->where('is_active', true)
                    ->orWhere('id', $booking->room_id);
            })
            ->orderBy('hotel_id')
            ->orderBy('name')
            ->get();

        return view('admin.bookings.edit', compact('booking', 'rooms'));
    }

    public function update(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate([
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'check_in' => ['required', 'date'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'guests' => ['required', 'integer', 'min:1', 'max:20'],
            'payment_method' => ['sometimes', 'required', Rule::in(array_keys(Booking::paymentMethods()))],
            'payment_provider' => ['nullable', Rule::requiredIf(fn (): bool => in_array($request->input('payment_method', $booking->payment_method), ['ewallet', 'bank_transfer'], true)), Rule::in(array_keys(Booking::paymentProvidersForMethod($request->input('payment_method', $booking->payment_method))))],
            'status' => ['required', Rule::in(['pending', 'confirmed', 'cancelled'])],
            'special_requests' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($booking, $data): void {
            $lockedBooking = Booking::query()->lockForUpdate()->findOrFail($booking->id);
            $room = Room::query()->lockForUpdate()->findOrFail($data['room_id']);

            if ($data['guests'] > $room->capacity) {
                throw ValidationException::withMessages([
                    'guests' => "This room accommodates up to {$room->capacity} guests.",
                ]);
            }

            if (in_array($data['status'], ['pending', 'confirmed'], true)) {
                $overlap = Booking::query()
                    ->where('id', '!=', $lockedBooking->id)
                    ->where('room_id', $room->id)
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->where('check_in', '<', $data['check_out'])
                    ->where('check_out', '>', $data['check_in'])
                    ->exists();

                if ($overlap) {
                    throw ValidationException::withMessages([
                        'check_in' => 'This room is already booked for the selected dates.',
                    ]);
                }
            }

            $nights = Carbon::parse($data['check_in'])->diffInDays(Carbon::parse($data['check_out']));
            $totalAmount = $nights * $room->price_per_night;
            $lockedBooking->update([
                'room_id' => $room->id,
                'check_in' => $data['check_in'],
                'check_out' => $data['check_out'],
                'guests' => $data['guests'],
                'total_amount' => $totalAmount,
                'payment_method' => $data['payment_method'] ?? $lockedBooking->payment_method,
                'payment_provider' => in_array($data['payment_method'] ?? $lockedBooking->payment_method, ['ewallet', 'bank_transfer'], true) ? ($data['payment_provider'] ?? $lockedBooking->payment_provider) : null,
                'payment_amount' => $totalAmount,
                'status' => $data['status'],
                'special_requests' => $data['special_requests'] ?? null,
            ]);
        });

        return redirect()
            ->route('admin.bookings.index')
            ->with('success', "Booking {$booking->confirmation_code} was updated.");
    }
}
