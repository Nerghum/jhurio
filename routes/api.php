<?php

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\TrackingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['api.auth'])->group(function () {
    Route::get('/ping', fn () => response()->json(['status' => 'pong']));
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::match(['put', 'patch'], '/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
    Route::post('/products/{product}/reviews', [ProductController::class, 'addReview']);
    Route::post('/products/{product}/reviews/batch', [ProductController::class, 'addReviewsBatch']);
    Route::post('/reviews', [ProductController::class, 'storeReviewDirect']);
});

// Analytics Tracking Endpoint
Route::post('/track', [TrackingController::class, 'track']);
