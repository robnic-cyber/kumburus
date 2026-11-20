<?php

namespace App\Http\Controllers;

use App\Mail\BookingConfirmedMail;
use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $bookings = $request->user()->bookings()->with('room.hotel')->latest()->get();

        return view('bookings.index', compact('bookings'));
    }

    public function create(Room $room): View
    {
        abort_unless($room->is_active && $room->hotel->is_active, 404);

        return view('bookings.create', compact('room'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'guests' => ['required', 'integer', 'min:1', 'max:20'],
            'payment_method' => ['required', Rule::in(array_keys(Booking::paymentMethods()))],
            'payment_provider' => ['nullable', Rule::requiredIf(fn (): bool => $request->input('payment_method') === 'ewallet'), Rule::in(array_keys(Booking::paymentProviders()))],
            'payment_amount' => ['required', 'numeric', 'min:0.01'],
            'special_requests' => ['nullable', 'string', 'max:2000'],
        ]);

        $booking = DB::transaction(function () use ($data, $request): Booking {
            $room = Room::query()->lockForUpdate()->with('hotel')->findOrFail($data['room_id']);

            if (! $room->is_active || ! $room->hotel->is_active) {
                throw ValidationException::withMessages(['room_id' => 'This room is no longer available.']);
            }

            $overlap = Booking::query()
                ->where('room_id', $room->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->where('check_in', '<', $data['check_out'])
                ->where('check_out', '>', $data['check_in'])
                ->exists();

            if ($overlap) {
                throw ValidationException::withMessages([
                    'check_in' => 'This room is already booked for some or all of those dates.',
                ]);
            }

            $nights = Carbon::parse($data['check_in'])->diffInDays(Carbon::parse($data['check_out']));
            $totalAmount = $nights * (float) $room->price_per_night;

            if ((float) $data['payment_amount'] > $totalAmount) {
                throw ValidationException::withMessages(['payment_amount' => 'The payment amount cannot be higher than the booking total.']);
            }

            return Booking::create([
                'user_id' => $request->user()->id,
                'room_id' => $room->id,
                'confirmation_code' => 'KMB-'.Str::upper(Str::random(8)),
                'check_in' => $data['check_in'],
                'check_out' => $data['check_out'],
                'guests' => $data['guests'],
                'total_amount' => $totalAmount,
                'payment_method' => $data['payment_method'],
                'payment_provider' => $data['payment_method'] === 'ewallet' ? $data['payment_provider'] : null,
                'payment_amount' => $data['payment_amount'],
                'status' => 'confirmed',
                'special_requests' => $data['special_requests'] ?? null,
            ]);
        });

        $booking->load(['user', 'room.hotel']);

        try {
            Mail::to($booking->user)->send(new BookingConfirmedMail($booking));
            $booking->update(['email_sent_at' => now()]);
        } catch (Throwable $exception) {
            Log::error('Booking confirmation email failed.', [
                'booking_id' => $booking->id,
                'exception' => $exception->getMessage(),
            ]);
        }

        return redirect()->route('bookings.show', $booking)->with('success', 'Booking confirmed. Check your email for the receipt.');
    }

    public function show(Request $request, Booking $booking): View
    {
        abort_unless($booking->user_id === $request->user()->id || $request->user()->is_admin, 403);
        $booking->load(['user', 'room.hotel']);

        return view('bookings.show', compact('booking'));
    }

    public function edit(Request $request, Booking $booking): View
    {
        $this->ensureGuestCanEdit($request, $booking);
        $booking->load(['user', 'room.hotel']);

        $rooms = Room::with('hotel')
            ->where(function ($query) use ($booking): void {
                $query->where('id', $booking->room_id)
                    ->orWhere(function ($query): void {
                        $query->where('is_active', true)
                            ->whereHas('hotel', fn ($hotelQuery) => $hotelQuery->where('is_active', true));
                    });
            })
            ->orderBy('hotel_id')
            ->orderBy('name')
            ->get();

        return view('bookings.edit', compact('booking', 'rooms'));
    }

    public function update(Request $request, Booking $booking): RedirectResponse
    {
        $this->ensureGuestCanEdit($request, $booking);

        $data = $request->validate([
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'guests' => ['required', 'integer', 'min:1', 'max:20'],
            'payment_method' => ['sometimes', 'required', Rule::in(array_keys(Booking::paymentMethods()))],
            'payment_provider' => ['nullable', Rule::requiredIf(fn (): bool => $request->input('payment_method', $booking->payment_method) === 'ewallet'), Rule::in(array_keys(Booking::paymentProviders()))],
            'payment_amount' => ['sometimes', 'required', 'numeric', 'min:0.01'],
            'special_requests' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($data, $booking, $request): void {
            $lockedBooking = Booking::query()->lockForUpdate()->findOrFail($booking->id);
            $room = Room::query()->with('hotel')->lockForUpdate()->findOrFail($data['room_id']);

            if ($lockedBooking->user_id !== $request->user()->id) {
                abort(403);
            }

            if ($data['room_id'] !== $lockedBooking->room_id && (! $room->is_active || ! $room->hotel->is_active)) {
                throw ValidationException::withMessages(['room_id' => 'This room is no longer available.']);
            }

            if ($data['guests'] > $room->capacity) {
                throw ValidationException::withMessages([
                    'guests' => "This room accommodates up to {$room->capacity} guests.",
                ]);
            }

            $overlap = Booking::query()
                ->where('id', '!=', $lockedBooking->id)
                ->where('room_id', $room->id)
                ->whereIn('status', ['pending', 'confirmed'])
                ->where('check_in', '<', $data['check_out'])
                ->where('check_out', '>', $data['check_in'])
                ->exists();

            if ($overlap) {
                throw ValidationException::withMessages([
                    'check_in' => 'This room is already booked for some or all of those dates.',
                ]);
            }

            $nights = Carbon::parse($data['check_in'])->diffInDays(Carbon::parse($data['check_out']));
            $totalAmount = $nights * (float) $room->price_per_night;
            $paymentAmount = (float) ($data['payment_amount'] ?? $lockedBooking->payment_amount);

            if ($paymentAmount > $totalAmount) {
                throw ValidationException::withMessages(['payment_amount' => 'The payment amount cannot be higher than the booking total.']);
            }

            $lockedBooking->update([
                'room_id' => $room->id,
                'check_in' => $data['check_in'],
                'check_out' => $data['check_out'],
                'guests' => $data['guests'],
                'total_amount' => $totalAmount,
                'payment_method' => $data['payment_method'] ?? $lockedBooking->payment_method,
                'payment_provider' => ($data['payment_method'] ?? $lockedBooking->payment_method) === 'ewallet' ? ($data['payment_provider'] ?? $lockedBooking->payment_provider) : null,
                'payment_amount' => $paymentAmount,
                'special_requests' => $data['special_requests'] ?? null,
            ]);
        });

        return redirect()
            ->route('bookings.show', $booking)
            ->with('success', 'Your booking was updated successfully.');
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->user_id === $request->user()->id || $request->user()->is_admin, 403);

        if ($booking->status === 'confirmed' && $booking->check_in->isFuture()) {
            $booking->update(['status' => 'cancelled']);
        }

        return back()->with('success', 'Booking status updated.');
    }

    private function ensureGuestCanEdit(Request $request, Booking $booking): void
    {
        abort_unless($booking->user_id === $request->user()->id, 403);
        abort_unless($booking->status !== 'cancelled' && $booking->check_in->isFuture(), 403);
    }
}
