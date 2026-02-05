<?php

use App\Http\Controllers\Admin\CategoryController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Admin routes - require authentication and admin role
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Category routes
    Route::resource('categories', CategoryController::class);

    // Add more admin routes here
    // Route::resource('products', ProductController::class);
    // Route::resource('users', UserController::class);
    // Route::resource('orders', OrderController::class);
});
