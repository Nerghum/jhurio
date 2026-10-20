<?php

use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\TrackingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['api.auth'])->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
});

// Analytics Tracking Endpoint
Route::post('/track', [TrackingController::class, 'track']);
