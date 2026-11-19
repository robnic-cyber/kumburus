<?php

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@kumburus.test'],
            ['name' => 'Kumburus Admin', 'password' => 'password', 'is_admin' => true]
        );

        User::updateOrCreate(
            ['email' => 'guest@kumburus.test'],
            ['name' => 'Demo Guest', 'password' => 'password', 'is_admin' => false]
        );

        $hotel = Hotel::updateOrCreate(
            ['slug' => 'kumburus-bay-resort'],
            [
                'name' => 'Kumburus Bay Resort',
                'description' => 'A calm coastal stay with warm service, bright rooms, and easy access to the waterfront.',
                'address' => '1 Kumburus Bay Road',
                'city' => 'Singapore',
                'country' => 'Singapore',
                'star_rating' => 5,
                'image_url' => 'https://images.unsplash.com/photo-1564501049412-61c2a3083791?auto=format&fit=crop&w=1200&q=80',
                'is_active' => true,
            ]
        );

        $rooms = [
            ['name' => 'Harbour King Room', 'room_number' => '101', 'description' => 'A king bed, work nook, and an open harbour view.', 'capacity' => 2, 'price_per_night' => 210, 'amenities' => ['King bed', 'Wi-Fi', 'Breakfast']],
            ['name' => 'Garden Family Suite', 'room_number' => '202', 'description' => 'A generous suite for families or small groups.', 'capacity' => 4, 'price_per_night' => 330, 'amenities' => ['Two beds', 'Living room', 'Pool access']],
            ['name' => 'Kumburus Penthouse', 'room_number' => '501', 'description' => 'Our signature suite with a private terrace.', 'capacity' => 3, 'price_per_night' => 540, 'amenities' => ['King bed', 'Terrace', 'Airport transfer']],
        ];

        foreach ($rooms as $room) {
            Room::updateOrCreate(
                ['hotel_id' => $hotel->id, 'room_number' => $room['room_number']],
                [...$room, 'is_active' => true]
            );
        }

        $admin->forceFill(['is_admin' => true])->save();
    }
}
