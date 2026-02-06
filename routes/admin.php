<?php

use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CarouselController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\LeagueController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SurfaceTypeController;
use App\Http\Controllers\Admin\TeamController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Admin routes - require authentication and admin role
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Category routes
    Route::resource('categories', CategoryController::class);

    // Brand routes
    Route::resource('brands', BrandController::class);

    // League routes
    Route::resource('leagues', LeagueController::class);

    // Team routes
    Route::resource('teams', TeamController::class);

    // Surface Type routes
    Route::resource('surface-types', SurfaceTypeController::class);

    // Product routes
    Route::resource('products', ProductController::class);

    // Carousel routes
    Route::resource('carousels', CarouselController::class);

    // Add more admin routes here
    // Route::resource('users', UserController::class);
    // Route::resource('orders', OrderController::class);
});
