<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_dashboard_and_edit_a_booking(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $guest = User::factory()->create();
        $hotel = $this->hotel();
        $originalRoom = $this->room($hotel, 'Garden Room', '101', 180);
        $updatedRoom = $this->room($hotel, 'Amber Suite', '202', 250);
        $booking = Booking::create([
            'user_id' => $guest->id,
            'room_id' => $originalRoom->id,
            'confirmation_code' => 'KMB-ADMIN-01',
            'check_in' => now()->addDays(5)->toDateString(),
            'check_out' => now()->addDays(8)->toDateString(),
            'guests' => 2,
            'total_amount' => 540,
            'status' => 'confirmed',
        ]);

        $dashboard = $this->actingAs($admin)->get(route('admin.dashboard'));
        $dashboard->assertOk();
        $dashboard->assertSee('Latest bookings');
        $dashboard->assertSee($booking->confirmation_code);

        $editPage = $this->actingAs($admin)->get(route('admin.bookings.edit', $booking));
        $editPage->assertOk();
        $editPage->assertSee('Update booking');

        $response = $this->actingAs($admin)->put(route('admin.bookings.update', $booking), [
            'room_id' => $updatedRoom->id,
            'check_in' => now()->addDays(10)->toDateString(),
            'check_out' => now()->addDays(12)->toDateString(),
            'guests' => 2,
            'status' => 'confirmed',
            'special_requests' => 'Late arrival',
        ]);

        $response->assertRedirect(route('admin.bookings.index'));
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'room_id' => $updatedRoom->id,
            'total_amount' => 500,
            'special_requests' => 'Late arrival',
        ]);
    }

    public function test_admin_booking_edit_rejects_an_overlapping_room(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $guest = User::factory()->create();
        $hotel = $this->hotel();
        $room = $this->room($hotel, 'Garden Room', '101', 180);
        $otherBooking = Booking::create([
            'user_id' => $guest->id,
            'room_id' => $room->id,
            'confirmation_code' => 'KMB-ADMIN-02',
            'check_in' => now()->addDays(10)->toDateString(),
            'check_out' => now()->addDays(12)->toDateString(),
            'guests' => 1,
            'total_amount' => 360,
            'status' => 'confirmed',
        ]);
        $booking = Booking::create([
            'user_id' => $guest->id,
            'room_id' => $room->id,
            'confirmation_code' => 'KMB-ADMIN-03',
            'check_in' => now()->addDays(5)->toDateString(),
            'check_out' => now()->addDays(8)->toDateString(),
            'guests' => 1,
            'total_amount' => 540,
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.bookings.update', $booking), [
            'room_id' => $room->id,
            'check_in' => now()->addDays(11)->toDateString(),
            'check_out' => now()->addDays(13)->toDateString(),
            'guests' => 1,
            'status' => 'confirmed',
        ]);

        $response->assertSessionHasErrors('check_in');
        $this->assertSame($room->id, $otherBooking->fresh()->room_id);
        $this->assertSame(now()->addDays(5)->toDateString(), $booking->fresh()->check_in->toDateString());
    }

    private function hotel(): Hotel
    {
        return Hotel::create([
            'name' => 'Admin Test Hotel',
            'slug' => 'admin-test-hotel',
            'address' => '1 Admin Street',
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
}
