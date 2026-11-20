<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRoomTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_a_room_without_bookings(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $room = $this->room();

        $response = $this->actingAs($admin)->delete(route('admin.rooms.destroy', $room));

        $response->assertSessionHas('success', 'Garden Room was deleted.');
        $this->assertDatabaseMissing('rooms', ['id' => $room->id]);
    }

    public function test_admin_cannot_delete_a_room_with_booking_records(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $guest = User::factory()->create();
        $room = $this->room();

        Booking::create([
            'user_id' => $guest->id,
            'room_id' => $room->id,
            'confirmation_code' => 'KMB-ROOM-01',
            'check_in' => now()->addDays(5)->toDateString(),
            'check_out' => now()->addDays(7)->toDateString(),
            'guests' => 1,
            'total_amount' => 360,
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.rooms.destroy', $room));

        $response->assertSessionHas('error', 'Garden Room cannot be deleted because it has booking records. Archive it instead.');
        $this->assertDatabaseHas('rooms', ['id' => $room->id]);
    }

    private function room(): Room
    {
        $hotel = Hotel::create([
            'name' => 'Room Test Hotel',
            'slug' => 'room-test-hotel',
            'address' => '1 Room Street',
            'city' => 'Manila',
            'country' => 'Philippines',
            'star_rating' => 4,
            'is_active' => true,
        ]);

        return Room::create([
            'hotel_id' => $hotel->id,
            'name' => 'Garden Room',
            'room_number' => '101',
            'capacity' => 2,
            'price_per_night' => 180,
            'amenities' => ['Wi-Fi'],
            'is_active' => true,
        ]);
    }
}
