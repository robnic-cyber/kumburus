<?php

namespace Tests\Feature;

use App\Mail\BookingConfirmedMail;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_guest_can_book_a_room_and_receive_confirmation_email(): void
    {
        Mail::fake();
        $user = User::factory()->create();
        $room = $this->room();
        $checkIn = now()->addDays(5)->toDateString();
        $checkOut = now()->addDays(8)->toDateString();

        $response = $this->actingAs($user)->post(route('bookings.store'), [
            'room_id' => $room->id,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'guests' => 2,
        ]);

        $booking = Booking::first();

        $response->assertRedirect(route('bookings.show', $booking));
        $this->assertDatabaseHas('bookings', [
            'user_id' => $user->id,
            'room_id' => $room->id,
            'status' => 'confirmed',
            'total_amount' => 600.00,
        ]);
        Mail::assertSent(BookingConfirmedMail::class, fn (BookingConfirmedMail $mail) => $mail->booking->id === $booking->id);
    }

    public function test_overlapping_booking_is_rejected(): void
    {
        $user = User::factory()->create();
        $room = $this->room();

        Booking::create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'confirmation_code' => 'KMB-EXISTING',
            'check_in' => now()->addDays(5)->toDateString(),
            'check_out' => now()->addDays(8)->toDateString(),
            'guests' => 2,
            'total_amount' => 600,
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs(User::factory()->create())->post(route('bookings.store'), [
            'room_id' => $room->id,
            'check_in' => now()->addDays(7)->toDateString(),
            'check_out' => now()->addDays(9)->toDateString(),
            'guests' => 1,
        ]);

        $response->assertSessionHasErrors('check_in');
        $this->assertCount(1, Booking::all());
    }

    private function room(): Room
    {
        $hotel = Hotel::create([
            'name' => 'Test Hotel',
            'slug' => 'test-hotel',
            'address' => '1 Test Street',
            'city' => 'Singapore',
            'country' => 'Singapore',
            'star_rating' => 4,
            'is_active' => true,
        ]);

        return Room::create([
            'hotel_id' => $hotel->id,
            'name' => 'Test Room',
            'room_number' => '101',
            'capacity' => 2,
            'price_per_night' => 200,
            'amenities' => ['Wi-Fi'],
            'is_active' => true,
        ]);
    }
}
