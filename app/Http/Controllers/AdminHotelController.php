<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminHotelController extends Controller
{
    public function index(): View
    {
        $hotels = Hotel::with('rooms')->withCount('rooms')->latest()->get();

        return view('admin.hotels.index', compact('hotels'));
    }

    public function create(): View
    {
        return view('admin.hotels.form', ['hotel' => new Hotel]);
    }

    public function store(Request $request): RedirectResponse
    {
        $hotel = Hotel::create($this->validated($request));

        return redirect()->route('admin.hotels.index')->with('success', "{$hotel->name} was created.");
    }

    public function edit(Hotel $hotel): View
    {
        return view('admin.hotels.form', compact('hotel'));
    }

    public function update(Request $request, Hotel $hotel): RedirectResponse
    {
        $hotel->update($this->validated($request, $hotel));

        return redirect()->route('admin.hotels.index')->with('success', "{$hotel->name} was updated.");
    }

    public function destroy(Hotel $hotel): RedirectResponse
    {
        $hotel->update(['is_active' => false]);

        return back()->with('success', "{$hotel->name} was archived.");
    }

    private function validated(Request $request, ?Hotel $hotel = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:5000'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'country' => ['required', 'string', 'max:100'],
            'star_rating' => ['required', 'integer', 'between:1,5'],
            'image_url' => ['nullable', 'url', 'max:500'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            $data['image_url'] = $request->file('image')->store('hotels', 'public');
        }

        unset($data['image']);

        $data['slug'] = Str::slug($data['name']).($hotel && $hotel->exists ? '-'.$hotel->id : '-'.Str::lower(Str::random(5)));
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
