<?php

use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CliController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\HomepageSectionController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\TrackingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['api.auth'])->group(function () {
    // Ping & Universal CLI Action Runner
    Route::get('/ping', [CliController::class, 'execute']);
    Route::post('/cli', [CliController::class, 'execute']);

    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/{identifier}', [ProductController::class, 'show']);
    Route::match(['put', 'patch'], '/products/{identifier}', [ProductController::class, 'update']);
    Route::delete('/products/{identifier}', [ProductController::class, 'destroy']);

    // Backward-compatible Product Reviews
    Route::post('/products/{product}/reviews', [ProductController::class, 'addReview']);
    Route::post('/products/{product}/reviews/batch', [ProductController::class, 'addReviewsBatch']);

    // Categories
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::get('/categories/{identifier}', [CategoryController::class, 'show']);
    Route::match(['put', 'patch'], '/categories/{identifier}', [CategoryController::class, 'update']);
    Route::delete('/categories/{identifier}', [CategoryController::class, 'destroy']);

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::match(['put', 'patch'], '/orders/{id}', [OrderController::class, 'update']);
    Route::delete('/orders/{id}', [OrderController::class, 'destroy']);

    // Reviews
    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::post('/reviews/batch', [ReviewController::class, 'storeBatch']);
    Route::get('/reviews/{id}', [ReviewController::class, 'show']);
    Route::match(['put', 'patch'], '/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);

    // Banners
    Route::get('/banners', [BannerController::class, 'index']);
    Route::post('/banners', [BannerController::class, 'store']);
    Route::get('/banners/{id}', [BannerController::class, 'show']);
    Route::match(['put', 'patch'], '/banners/{id}', [BannerController::class, 'update']);
    Route::delete('/banners/{id}', [BannerController::class, 'destroy']);

    // Homepage Builder Sections
    Route::get('/homepage-sections', [HomepageSectionController::class, 'index']);
    Route::post('/homepage-sections', [HomepageSectionController::class, 'store']);
    Route::post('/homepage-sections/hero', [HomepageSectionController::class, 'updateHero']);
    Route::get('/homepage-sections/{identifier}', [HomepageSectionController::class, 'show']);
    Route::match(['put', 'patch'], '/homepage-sections/{identifier}', [HomepageSectionController::class, 'update']);
    Route::delete('/homepage-sections/{identifier}', [HomepageSectionController::class, 'destroy']);

    // Settings
    Route::get('/settings', [SettingController::class, 'index']);
    Route::post('/settings', [SettingController::class, 'update']);
    Route::get('/settings/{key}', [SettingController::class, 'show']);

    // Media
    Route::get('/media', [MediaController::class, 'index']);
    Route::post('/media', [MediaController::class, 'store']);
    Route::get('/media/{id}', [MediaController::class, 'show']);
    Route::delete('/media/{id}', [MediaController::class, 'destroy']);

    // Coupons
    Route::get('/coupons', [CouponController::class, 'index']);
    Route::post('/coupons', [CouponController::class, 'store']);
    Route::get('/coupons/{id}', [CouponController::class, 'show']);
    Route::match(['put', 'patch'], '/coupons/{id}', [CouponController::class, 'update']);
    Route::delete('/coupons/{id}', [CouponController::class, 'destroy']);

    // Stock
    Route::get('/stock', [StockController::class, 'index']);
    Route::post('/stock/adjust', [StockController::class, 'adjust']);
    Route::post('/stock/purchases', [StockController::class, 'storePurchase']);
});

// Analytics Tracking Endpoint
Route::post('/track', [TrackingController::class, 'track']);
