<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminDashboardController;

// Redirect root to Admin Dashboard
Route::get('/', function () {
    return redirect()->route('admin.login');
});

// Admin Panel Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminDashboardController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminDashboardController::class, 'login'])->name('login.post');
    Route::post('/logout', [AdminDashboardController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminDashboardController::class, 'users'])->name('users');
    Route::post('/users/{id}/toggle', [AdminDashboardController::class, 'toggleUserStatus'])->name('users.toggle');
    Route::get('/trips', [AdminDashboardController::class, 'trips'])->name('trips');
    Route::get('/shipments', [AdminDashboardController::class, 'shipments'])->name('shipments');
    
    // Banners & Ads
    Route::get('/banners', [AdminDashboardController::class, 'banners'])->name('banners');
    Route::post('/banners', [AdminDashboardController::class, 'storeBanner'])->name('banners.store');
    Route::post('/banners/{id}/toggle', [AdminDashboardController::class, 'toggleBanner'])->name('banners.toggle');
    Route::delete('/banners/{id}', [AdminDashboardController::class, 'deleteBanner'])->name('banners.delete');

    // Borders Live Status Control
    Route::post('/borders/{id}/update', [AdminDashboardController::class, 'updateBorder'])->name('borders.update');
});
