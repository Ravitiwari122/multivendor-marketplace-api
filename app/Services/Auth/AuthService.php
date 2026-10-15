<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{

    public function register(
        string $name,
        string $email,
        string $password
    ): User {
        return User::create([
            "name" => $name,
            "email" => $email,
            "password" => Hash::make($password),
            "role" => "customer",
        ]);
    }


    public function login(string $email, string $password): array
    {
        $user = User::where("email", $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw new \Exception("Invalid credentials");
        }

        $token = $user->createToken("auth_token")->plainTextToken;

        return [
            "user" => $user,
            "token" => $token,
        ];
    }
}
