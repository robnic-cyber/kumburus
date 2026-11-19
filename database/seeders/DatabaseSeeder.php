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

        $hotels = [
            [
                'slug' => 'kumburus-bay-resort',
                'name' => 'Kumburus Bay Resort',
                'description' => 'A calm coastal stay with warm service, bright rooms, and easy access to the waterfront.',
                'address' => '1 White Beach Road',
                'city' => 'Boracay',
                'country' => 'Philippines',
                'star_rating' => 5,
                'image_url' => 'https://images.unsplash.com/photo-1564501049412-61c2a3083791?auto=format&fit=crop&w=1200&q=80',
                'is_active' => true,
                'rooms' => [
                    ['name' => 'Harbour King Room', 'room_number' => '101', 'description' => 'A king bed, work nook, and a bright view of the bay.', 'capacity' => 2, 'price_per_night' => 4800, 'amenities' => ['King bed', 'Wi-Fi', 'Breakfast']],
                    ['name' => 'Garden Family Suite', 'room_number' => '202', 'description' => 'A generous suite for families or small groups.', 'capacity' => 4, 'price_per_night' => 7500, 'amenities' => ['Two beds', 'Living room', 'Pool access']],
                    ['name' => 'Kumburus Penthouse', 'room_number' => '501', 'description' => 'Our signature suite with a private terrace and sunset views.', 'capacity' => 3, 'price_per_night' => 12500, 'amenities' => ['King bed', 'Terrace', 'Airport transfer']],
                ],
            ],
            [
                'slug' => 'amihan-verde-hotel',
                'name' => 'Amihan Verde Hotel',
                'description' => 'A leafy city retreat with thoughtful rooms, local breakfast, and easy access to Cebu favourites.',
                'address' => '18 Amihan Avenue',
                'city' => 'Cebu City',
                'country' => 'Philippines',
                'star_rating' => 4,
                'image_url' => 'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?auto=format&fit=crop&w=1200&q=80',
                'is_active' => true,
                'rooms' => [
                    ['name' => 'Verde Queen Room', 'room_number' => '110', 'description' => 'A quiet queen room with soft natural light.', 'capacity' => 2, 'price_per_night' => 3500, 'amenities' => ['Queen bed', 'Wi-Fi', 'Work desk']],
                    ['name' => 'Cebu Corner Suite', 'room_number' => '215', 'description' => 'A spacious corner suite for longer city stays.', 'capacity' => 3, 'price_per_night' => 5900, 'amenities' => ['King bed', 'Sofa bed', 'Breakfast']],
                    ['name' => 'Amihan Grand Suite', 'room_number' => '402', 'description' => 'A polished suite with a lounge area and city views.', 'capacity' => 4, 'price_per_night' => 9800, 'amenities' => ['Two beds', 'Living room', 'Pool access']],
                ],
            ],
            [
                'slug' => 'amber-grove-retreat',
                'name' => 'Amber Grove Retreat',
                'description' => 'A warm hillside hideaway for slow mornings, cool evenings, and generous shared spaces.',
                'address' => '7 Ridgeview Drive',
                'city' => 'Tagaytay',
                'country' => 'Philippines',
                'star_rating' => 4,
                'image_url' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200&q=80',
                'is_active' => true,
                'rooms' => [
                    ['name' => 'Grove Queen Room', 'room_number' => '103', 'description' => 'A cosy room with a garden-facing window.', 'capacity' => 2, 'price_per_night' => 3900, 'amenities' => ['Queen bed', 'Garden view', 'Wi-Fi']],
                    ['name' => 'Amber Family Room', 'room_number' => '204', 'description' => 'A relaxed room for family weekends and small groups.', 'capacity' => 4, 'price_per_night' => 6800, 'amenities' => ['Two beds', 'Breakfast', 'Garden access']],
                    ['name' => 'Grove Villa', 'room_number' => '305', 'description' => 'A private villa with a sitting room and outdoor bath.', 'capacity' => 4, 'price_per_night' => 10500, 'amenities' => ['King bed', 'Private patio', 'Outdoor bath']],
                ],
            ],
            [
                'slug' => 'luntian-palms-lodge',
                'name' => 'Luntian Palms Lodge',
                'description' => 'A small island lodge surrounded by palms, clear water, and an unhurried pace.',
                'address' => '42 Palms Coast Road',
                'city' => 'Puerto Princesa',
                'country' => 'Philippines',
                'star_rating' => 5,
                'image_url' => 'https://images.unsplash.com/photo-1540541338287-41700207dee6?auto=format&fit=crop&w=1200&q=80',
                'is_active' => true,
                'rooms' => [
                    ['name' => 'Palm Garden Room', 'room_number' => '106', 'description' => 'A fresh room with a private garden terrace.', 'capacity' => 2, 'price_per_night' => 5200, 'amenities' => ['Queen bed', 'Terrace', 'Breakfast']],
                    ['name' => 'Luntian Pool Suite', 'room_number' => '208', 'description' => 'A cool, generous suite just steps from the pool.', 'capacity' => 3, 'price_per_night' => 8900, 'amenities' => ['King bed', 'Pool access', 'Minibar']],
                    ['name' => 'Palms Beach Villa', 'room_number' => '309', 'description' => 'A private villa made for long island weekends.', 'capacity' => 4, 'price_per_night' => 14500, 'amenities' => ['Two beds', 'Beach access', 'Airport transfer']],
                ],
            ],
        ];

        foreach ($hotels as $hotelData) {
            $rooms = $hotelData['rooms'];
            unset($hotelData['rooms']);

            $hotel = Hotel::updateOrCreate(['slug' => $hotelData['slug']], $hotelData);

            foreach ($rooms as $room) {
            Room::updateOrCreate(
                ['hotel_id' => $hotel->id, 'room_number' => $room['room_number']],
                [...$room, 'is_active' => true]
            );
            }
        }

        $admin->forceFill(['is_admin' => true])->save();
    }
}
