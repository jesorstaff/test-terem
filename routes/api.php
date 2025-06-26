<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\BookingController;

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/rooms', [RoomController::class, 'index']);
Route::middleware('auth:sanctum')->post('/bookings', [BookingController::class, 'store']); 