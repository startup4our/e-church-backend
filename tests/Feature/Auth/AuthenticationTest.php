<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_authenticate_using_the_login_endpoint(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'status' => \App\Enums\UserStatus::ACTIVE,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'user',
                         'permissions',
                         'areas',
                         'access_token',
                         'token_type'
                     ]
                 ]);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'status' => \App\Enums\UserStatus::ACTIVE,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
                 ->assertJsonStructure([
                     'success',
                     'error' => [
                         'code',
                         'message',
                         'details',
                         'timestamp'
                     ]
                 ])
                 ->assertJson([
                     'success' => false,
                     'error' => [
                         'code' => 'INVALID_CREDENTIALS'
                     ]
                 ]);
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'status' => \App\Enums\UserStatus::ACTIVE,
        ]);

        // login pra pegar token
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $token = $loginResponse->json('data.access_token');

        // usa token pra logout
        $logoutResponse = $this->withHeader('Authorization', 'Bearer '.$token)
                               ->postJson('/api/v1/auth/logout');

        $logoutResponse->assertStatus(200)
                       ->assertJsonStructure([
                           'success',
                           'data' => ['message']
                       ])
                       ->assertJson([
                           'success' => true,
                           'data' => [
                               'message' => 'Logout feito com sucesso'
                           ]
                       ]);
    }
}
