<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function login(array $credentials): ?string
    {
        $token = auth('api')->attempt($credentials);

        return $token ?: null;
    }
}

