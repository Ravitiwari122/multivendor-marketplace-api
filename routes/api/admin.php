<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\ProductController;

Route::middleware(['auth:sanctum', 'role:admin'])->get('/admin/test', function (Request $request) {
    return response()->json([
        "success" => true,
        "message" => "Welcome to the admin dashboard",
        "user" => $request->user(),
        "role" => $request->user()->role,
    ]);
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('admin/categories', [CategoryController::class, 'index']);
    Route::post('admin/categories', [CategoryController::class, 'store']);
    Route::delete('admin/categories/{id}', [CategoryController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('admin/products', [ProductController::class, 'index']);
    Route::post('admin/products', [ProductController::class, 'store']);
    Route::put('admin/products/{id}', [ProductController::class, 'update']);
    Route::delete('admin/products/{id}', [ProductController::class, 'destroy']);
    Route::patch('admin/products/{id}/status', [ProductController::class, 'updateStatus']);
});
