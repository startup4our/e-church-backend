<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_permission()
    {
        $user = User::factory()->create();

        $response = $this->postJson('api/v1/permission', [
            'user_id' => $user->id,
            'create_scale' => true,
            'read_scale' => true,
            'manage_users' => true,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('permission', [
            'user_id' => $user->id,
            'create_scale' => true,
        ]);
    }
}
