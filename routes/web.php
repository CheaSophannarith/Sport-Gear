<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

// Admin routes - require authentication and admin role
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Add more admin routes here
    // Route::resource('products', ProductController::class);
    // Route::resource('users', UserController::class);
    // Route::resource('orders', OrderController::class);
});

require __DIR__.'/settings.php';
