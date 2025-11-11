<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;
use App\Models\Permission;
use App\Models\Church;

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

    protected function createUserWithPermissions(array $permissions = []): User
    {
        $church = Church::factory()->create();
        $user = User::factory()->create(['church_id' => $church->id]);
        
        // Create permission record with all permissions set to false by default
        $permissionData = [
            'user_id' => $user->id,
            'create_scale' => false,
            'read_scale' => false,
            'update_scale' => false,
            'delete_scale' => false,
            'create_music' => false,
            'read_music' => false,
            'update_music' => false,
            'delete_music' => false,
            'create_role' => false,
            'read_role' => false,
            'update_role' => false,
            'delete_role' => false,
            'create_area' => false,
            'read_area' => false,
            'update_area' => false,
            'delete_area' => false,
            'create_chat' => false,
            'read_chat' => false,
            'update_chat' => false,
            'delete_chat' => false,
            'manage_users' => false,
            'manage_handouts' => false,
            'manage_church_settings' => false,
            'manage_app_settings' => false,
        ];

        // Set specific permissions to true
        foreach ($permissions as $permission) {
            if (isset($permissionData[$permission])) {
                $permissionData[$permission] = true;
            }
        }

        Permission::create($permissionData);
        
        return $user;
    }
}
