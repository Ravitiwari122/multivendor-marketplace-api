<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Services\Auth\AuthService;

class AuthController extends Controller
{

    public function __construct(
        private AuthService $authService
    ) {}

    public function register(RegisterRequest $request)
    {
        $user = $this->authService->register(
            $request->name,
            $request->email,
            $request->password
        );

        return response()->json([
            "success" => true,
            "message" => "User registered successfully",
            "data" => [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
            ],
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        try {
            $result = $this->authService->login($request->email, $request->password);

            return response()->json([
                "success" => true,
                "message" => "User logged in successfully",
                "data" => [
                    "user" => [
                        "id" => $result["user"]->id,
                        "name" => $result["user"]->name,
                        "email" => $result["user"]->email,
                    ],
                    "token" => $result["token"],
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => $e->getMessage(),
            ], 401);
        }
    }
}
