<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('web')->prefix('/api')->group(function () {
    Route::post('/register', [Api\AuthController::class, 'register'])->name('api.auth.register');
    Route::post('/login', [Api\AuthController::class, 'login'])->name('api.auth.login');
    Route::post('/logout', [Api\AuthController::class, 'logout'])->name('api.auth.logout');
});
