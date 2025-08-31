<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function authenticate(User $user = null): string
    {
        $user = $user ?? User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $this->withHeader('Authorization', "Bearer {$token}");

        return $token;
    }
}
