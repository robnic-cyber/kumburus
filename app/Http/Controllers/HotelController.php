<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HotelController extends Controller
{
    public function index(Request $request): View
    {
        $hotels = Hotel::query()
            ->where('is_active', true)
            ->when($request->filled('city'), fn ($query) => $query->where('city', 'like', '%'.$request->string('city').'%'))
            ->withCount(['rooms' => fn ($query) => $query->where('is_active', true)])
            ->latest()
            ->get();

        return view('hotels.index', compact('hotels'));
    }

    public function show(Hotel $hotel): View
    {
        abort_unless($hotel->is_active || auth()->user()?->is_admin, 404);

        $hotel->load(['rooms' => fn ($query) => $query->where('is_active', true)->orderBy('price_per_night')]);

        return view('hotels.show', compact('hotel'));
    }
}
