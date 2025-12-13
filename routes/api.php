<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\MovieController;
use App\Http\Controllers\Api\ValueController;
use App\Http\Controllers\Api\AuthController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/bookings/{id}', [BookingController::class, 'show']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::put('/bookings/{id}', [BookingController::class, 'update']);
    Route::delete('/bookings/{id}', [BookingController::class, 'destroy']);
    Route::get('/booked_seats/{party_date}/{movie_id}', [BookingController::class, 'booked_seats']);
    Route::get('/movies/{id}', [MovieController::class, 'show']);
});

//Route::middleware('admin')->group(function () {
    // Admin
    Route::get('/settings', [ValueController::class, 'index']);
    Route::get('/settings/{id}', [ValueController::class, 'show']);
    Route::post('/settings', [ValueController::class, 'update']);
    Route::delete('/settings/{id}', [ValueController::class, 'destroy']);

    Route::post('/movies', [MovieController::class, 'store']);
    Route::put('/movies/{id}', [MovieController::class, 'update']);
    Route::delete('/movies/{id}', [MovieController::class, 'destroy']);
//});

Route::get('/movies', [MovieController::class, 'index']);

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);