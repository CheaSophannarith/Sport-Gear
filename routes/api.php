<?php

use App\Http\Controllers\Api\CarouselController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FilterController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

// Public API routes for frontend (Nuxt.js)
Route::prefix('v1')->name('api.v1.')->group(function () {
    // Categories for navigation/header
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/{slug}', [CategoryController::class, 'show'])->name('categories.show');

    // Carousel for homepage
    Route::get('/carousels', [CarouselController::class, 'index'])->name('carousels.index');

    // Featured products by category
    Route::get('/products/featured/{slug}', [ProductController::class, 'featuredProducts'])->name('products.featured');

    Route::get('/products/{slug}', [ProductController::class, 'allProducts'])->name('products.show');

    Route::get('/products/{slug}/filters', [ProductController::class, 'getFilters'])->name('products.filters');

    Route::group(['prefix' => 'filters', 'as' => 'filters.'], function () {
        Route::get('/brands', [FilterController::class, 'getBrands'])->name('brands');
        Route::get('/leagues', [FilterController::class, 'getLeagues'])->name('leagues');
        Route::get('/surface-types', [FilterController::class, 'getSurfaceTypes'])->name('surface-types');
        Route::get('/teams', [FilterController::class, 'getTeams'])->name('teams');
    });
});

// API routes for admin panel (cascading filters, dynamic data)
// Using 'web' middleware for session-based authentication (Inertia.js)
Route::middleware(['web', 'auth', 'verified', 'admin'])->prefix('admin')->name('api.admin.')->group(function () {
    // Get teams filtered by league(s)
    Route::get('/teams', [FilterController::class, 'getTeams'])->name('teams');

    // Get category details (filters + variant sizes)
    Route::get('/categories/{category}', [FilterController::class, 'getCategoryDetails'])->name('category.details');

    // Get all filter options for dropdown
    Route::get('/filters/brands', [FilterController::class, 'getBrands'])->name('filters.brands');
    Route::get('/filters/leagues', [FilterController::class, 'getLeagues'])->name('filters.leagues');
    Route::get('/filters/surface-types', [FilterController::class, 'getSurfaceTypes'])->name('filters.surface-types');
});
