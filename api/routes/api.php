<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api;

Route::get('/rooms', [Api\RoomController::class, 'index'])
    ->name('api.room.index');
