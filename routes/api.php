<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TripController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ShipmentController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\BorderStatusController;

// ---------------- Public Routes ----------------
Route::prefix('auth')->group(function () {
    Route::post('/request-otp', [AuthController::class, 'requestOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
});

Route::get('/borders/status', [BorderStatusController::class, 'index']);
Route::get('/shipments/track/{code}', [ShipmentController::class, 'track']);
Route::get('/trips', [TripController::class, 'index']);
Route::get('/trips/{id}', [TripController::class, 'show']);

// ---------------- Authenticated Routes ----------------
Route::middleware('auth:sanctum')->group(function () {
    // User Profile
    Route::get('/user/profile', [AuthController::class, 'getProfile']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    Route::post('/user/switch-role', [AuthController::class, 'switchRole']);

    // Trips (Waddini - وديني)
    Route::post('/trips', [TripController::class, 'store']);
    Route::get('/trips-mine', [TripController::class, 'myTrips']);

    // Bookings (حجز المقاعد)
    Route::post('/trips/{id}/book', [BookingController::class, 'bookSeat']);
    Route::put('/bookings/{id}/status', [BookingController::class, 'updateStatus']);
    Route::get('/bookings-mine', [BookingController::class, 'myBookings']);
    Route::get('/driver/bookings', [BookingController::class, 'driverBookings']);

    // Shipments (Wassini & Amanat - وصيني وأمانات)
    Route::get('/shipments', [ShipmentController::class, 'index']);
    Route::post('/shipments', [ShipmentController::class, 'store']);
    Route::put('/shipments/{id}/assign', [ShipmentController::class, 'assign']);
    Route::put('/shipments/{id}/reject', [ShipmentController::class, 'reject']);

    // Chat (دردشة وتنسيق الرحلة)
    Route::get('/chat/conversations', [ChatController::class, 'getConversations']);
    Route::get('/trips/{id}/messages', [ChatController::class, 'getMessages']);
    Route::post('/trips/{id}/messages', [ChatController::class, 'sendMessage']);

    // Notifications (الإشعارات)
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    // Borders Crowdsourcing Report
    Route::post('/borders/report', [BorderStatusController::class, 'report']);
});
