<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminHotelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_hotel_with_an_uploaded_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.hotels.store'), [
            'name' => 'Luntian Bay Hotel',
            'description' => 'A quiet stay by the water.',
            'address' => '1 Bay Road',
            'city' => 'Manila',
            'country' => 'Philippines',
            'star_rating' => 5,
            'image' => UploadedFile::fake()->create('luntian-bay.jpg', 100, 'image/jpeg'),
        ]);

        $hotel = Hotel::where('name', 'Luntian Bay Hotel')->firstOrFail();

        $response->assertRedirect(route('admin.hotels.index'));
        $this->assertStringStartsWith('hotels/', $hotel->getRawOriginal('image_url'));
        Storage::disk('public')->assertExists($hotel->getRawOriginal('image_url'));
    }
}
