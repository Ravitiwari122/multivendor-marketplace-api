<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Support\Facades\Route;


Route::post('/auth/login', [AuthController::class, 'login']);

Route::post('/auth/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->get('/auth/me', function (Request $request) {
    return response()->json([
        "success" => true,
        "data" => $request->user(),
    ]);
});

Route::middleware('auth:sanctum')->post('/auth/logout', function (Request $request) {
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        "success" => true,
        "message" => "User logged out successfully",
    ]);
});

Route::middleware(['auth:sanctum', 'role:customer'])->get('/auth/customer-test', function (Request $request) {
    return response()->json([
        "success" => true,
        "message" => "Welcome to the customer dashboard",
        "user" => $request->user(),
    ]);
});
