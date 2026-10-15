<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Seller\ProductController;

Route::middleware(['auth:sanctum', 'role:seller'])->get('/seller/test', function (Request $request) {
    return response()->json([
        "success" => true,
        "message" => "Welcome to the seller dashboard",
        "user" => $request->user(),
        "role" => $request->user()->role,
    ]);
});

Route::middleware(['auth:sanctum', 'role:seller'])->group(function () {
    Route::get('/seller/products', [ProductController::class, 'index']);
    Route::post('/seller/products', [ProductController::class, 'store']);
    Route::put('/seller/products/{id}', [ProductController::class, 'update']);
    Route::delete('/seller/products/{id}', [ProductController::class, 'destroy']);
});
