<?php

namespace App\Services;

use App\Models\User;
use App\Services\Interfaces\IAuthService;


class AuthService implements IAuthService{

    public function login(array $credentials) : false|array
    {
        $token = auth()->attempt($credentials);
        if (!$token) {
            return [
                'success' => false,
                'error' => 'Unauthorized'
            ];
        }

        $user = auth()->user();

        return [
            'success' => true,
            'user' => $user,
            'access_token' => $token,
            'expires_in' => auth()->user()->token()->expires_at,
            'type' => 'bearer',
        ];
    }
    
}