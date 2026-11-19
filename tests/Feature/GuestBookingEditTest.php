<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestBookingEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_edit_a_future_booking_and_total_is_recalculated(): void
    {
        $guest = User::factory()->create();
        $hotel = $this->hotel();
        $originalRoom = $this->room($hotel, 'Garden Room', '101', 180);
        $updatedRoom = $this->room($hotel, 'Amber Suite', '202', 250);
        $booking = $this->booking($guest, $originalRoom, 'KMB-GUEST-01', 5, 8, 540);

        $editPage = $this->actingAs($guest)->get(route('bookings.edit', $booking));
        $editPage->assertOk();
        $editPage->assertSee('Plans changed?');

        $response = $this->actingAs($guest)->put(route('bookings.update', $booking), [
            'room_id' => $updatedRoom->id,
            'check_in' => now()->addDays(10)->toDateString(),
            'check_out' => now()->addDays(12)->toDateString(),
            'guests' => 2,
            'special_requests' => 'Quiet room, please',
        ]);

        $response->assertRedirect(route('bookings.show', $booking));
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'room_id' => $updatedRoom->id,
            'total_amount' => 500,
            'special_requests' => 'Quiet room, please',
        ]);
    }

    public function test_guest_cannot_edit_another_guests_booking(): void
    {
        $owner = User::factory()->create();
        $otherGuest = User::factory()->create();
        $room = $this->room($this->hotel(), 'Garden Room', '101', 180);
        $booking = $this->booking($owner, $room, 'KMB-GUEST-02', 5, 8, 540);

        $response = $this->actingAs($otherGuest)->get(route('bookings.edit', $booking));

        $response->assertForbidden();
    }

    private function hotel(): Hotel
    {
        return Hotel::create([
            'name' => 'Guest Test Hotel',
            'slug' => 'guest-test-hotel',
            'address' => '1 Guest Street',
            'city' => 'Manila',
            'country' => 'Philippines',
            'star_rating' => 4,
            'is_active' => true,
        ]);
    }

    private function room(Hotel $hotel, string $name, string $number, int $price): Room
    {
        return Room::create([
            'hotel_id' => $hotel->id,
            'name' => $name,
            'room_number' => $number,
            'capacity' => 2,
            'price_per_night' => $price,
            'amenities' => ['Wi-Fi'],
            'is_active' => true,
        ]);
    }

    private function booking(User $user, Room $room, string $code, int $start, int $end, int $total): Booking
    {
        return Booking::create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'confirmation_code' => $code,
            'check_in' => now()->addDays($start)->toDateString(),
            'check_out' => now()->addDays($end)->toDateString(),
            'guests' => 2,
            'total_amount' => $total,
            'status' => 'confirmed',
        ]);
    }
}
