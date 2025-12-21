<?php

namespace App\Services;

class AuthService
{
    public function login(array $credentials): ?string
    {
        return auth('api')->attempt($credentials) ?: null;
    }
}


