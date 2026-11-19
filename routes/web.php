<?php

use App\Http\Controllers\AdminBookingController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminHotelController;
use App\Http\Controllers\AdminRoomController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\HotelController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HotelController::class, 'index'])->name('home');
Route::get('/hotels/{hotel}', [HotelController::class, 'show'])->name('hotels.show');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create/{room}', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function (): void {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}/edit', [AdminBookingController::class, 'edit'])->name('bookings.edit');
    Route::put('/bookings/{booking}', [AdminBookingController::class, 'update'])->name('bookings.update');

    Route::get('/hotels', [AdminHotelController::class, 'index'])->name('hotels.index');
    Route::get('/hotels/create', [AdminHotelController::class, 'create'])->name('hotels.create');
    Route::post('/hotels', [AdminHotelController::class, 'store'])->name('hotels.store');
    Route::get('/hotels/{hotel}/edit', [AdminHotelController::class, 'edit'])->name('hotels.edit');
    Route::put('/hotels/{hotel}', [AdminHotelController::class, 'update'])->name('hotels.update');
    Route::delete('/hotels/{hotel}', [AdminHotelController::class, 'destroy'])->name('hotels.destroy');

    Route::get('/hotels/{hotel}/rooms/create', [AdminRoomController::class, 'create'])->name('rooms.create');
    Route::post('/hotels/{hotel}/rooms', [AdminRoomController::class, 'store'])->name('rooms.store');
    Route::get('/rooms/{room}/edit', [AdminRoomController::class, 'edit'])->name('rooms.edit');
    Route::put('/rooms/{room}', [AdminRoomController::class, 'update'])->name('rooms.update');
    Route::delete('/rooms/{room}', [AdminRoomController::class, 'destroy'])->name('rooms.destroy');
});
