<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api;

Route::get('/rooms', [Api\RoomController::class, 'index'])->name('api.room.index');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [Api\AuthController::class, 'user'])->name('api.auth.user');
    Route::post('/bookings', [Api\BookingController::class, 'store'])->name('api.booking.store');
    Route::get('/bookings', [Api\BookingController::class, 'index'])->name('api.booking.index');
    Route::patch('/bookings/{id}/cancel', [Api\BookingController::class, 'cancel'])->name('api.booking.cancel');
});
