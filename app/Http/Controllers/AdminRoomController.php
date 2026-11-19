<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminRoomController extends Controller
{
    public function create(Hotel $hotel): View
    {
        return view('admin.rooms.form', ['hotel' => $hotel, 'room' => new Room]);
    }

    public function store(Request $request, Hotel $hotel): RedirectResponse
    {
        $hotel->rooms()->create($this->validated($request));

        return redirect()->route('admin.hotels.index')->with('success', "Room added to {$hotel->name}.");
    }

    public function edit(Room $room): View
    {
        return view('admin.rooms.form', ['hotel' => $room->hotel, 'room' => $room]);
    }

    public function update(Request $request, Room $room): RedirectResponse
    {
        $room->update($this->validated($request));

        return redirect()->route('admin.hotels.index')->with('success', "{$room->name} was updated.");
    }

    public function destroy(Room $room): RedirectResponse
    {
        $room->update(['is_active' => false]);

        return back()->with('success', "{$room->name} was archived.");
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'room_number' => ['required', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:3000'],
            'capacity' => ['required', 'integer', 'between:1,20'],
            'price_per_night' => ['required', 'numeric', 'min:0'],
            'amenities' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['amenities'] = collect(explode(',', (string) ($data['amenities'] ?? '')))
            ->map(fn (string $amenity) => trim($amenity))
            ->filter()
            ->values()
            ->all();
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
