<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['api.auth'])->group(function () {
    Route::post('/products', [\App\Http\Controllers\Api\ProductController::class, 'store']);
});
