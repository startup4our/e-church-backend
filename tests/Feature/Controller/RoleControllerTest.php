<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Area;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_roles()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        Role::factory()->count(2)->create();

        $response = $this->getJson('/api/v1/roles');

        $response->assertStatus(200)
                 ->assertJsonCount(2);
    }

    public function test_show_returns_role()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $role = Role::factory()->create();

        $response = $this->getJson("/api/v1/roles/{$role->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $role->id]);
    }

    public function test_store_creates_role()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $area = Area::factory()->create();

        $data = [
            'name'        => 'Regente',
            'description' => 'Coordena o coro',
            'area_id'     => $area->id,
        ];

        $response = $this->postJson('/api/v1/roles', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['name' => 'Regente']);

        $this->assertDatabaseHas('role', ['name' => 'Regente', 'area_id' => $area->id]);
    }

    public function test_update_modifies_role()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $role = Role::factory()->create(['name' => 'Antiga']);

        $response = $this->putJson("/api/v1/roles/{$role->id}", [
            'name' => 'Atualizada'
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Atualizada']);

        $this->assertDatabaseHas('role', ['id' => $role->id, 'name' => 'Atualizada']);
    }

    public function test_destroy_deletes_role()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $role = Role::factory()->create();

        $response = $this->deleteJson("/api/v1/roles/{$role->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('role', ['id' => $role->id]);
    }

    public function test_index_returns_roles_with_proper_structure()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        Role::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/roles');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         '*' => ['id', 'name', 'description', 'area_id']
                     ]
                 ])
                 ->assertJsonCount(3, 'data');
    }

    public function test_store_requires_valid_area_when_creating_role()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $data = [
            'name' => 'Test Role',
            'description' => 'Description',
            'area_id' => 99999, // Non-existent area
        ];

        $response = $this->postJson('/api/v1/roles', $data);

        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'success',
                     'error' => [
                         'code',
                         'message',
                         'details'
                     ]
                 ])
                 ->assertJson([
                     'success' => false,
                     'error' => [
                         'code' => 'VALIDATION_ERROR'
                     ]
                 ]);
    }

    public function test_update_requires_valid_area_if_provided()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $role = Role::factory()->create();

        $response = $this->putJson("/api/v1/roles/{$role->id}", [
            'area_id' => 99999
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure([
                     'success',
                     'error' => [
                         'code',
                         'message',
                         'details'
                     ]
                 ])
                 ->assertJson([
                     'success' => false,
                     'error' => [
                         'code' => 'VALIDATION_ERROR'
                     ]
                 ]);
    }

    public function test_show_returns_404_for_nonexistent_role()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $response = $this->getJson('/api/v1/roles/99999');

        $response->assertStatus(404)
                 ->assertJsonStructure([
                     'success',
                     'error' => [
                         'code',
                         'message'
                     ]
                 ])
                 ->assertJson([
                     'success' => false,
                     'error' => [
                         'code' => 'RESOURCE_NOT_FOUND'
                     ]
                 ]);
    }

    public function test_destroy_removes_role_from_database()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $role = Role::factory()->create();

        $response = $this->deleteJson("/api/v1/roles/{$role->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('role', ['id' => $role->id]);
        $this->assertNull(Role::find($role->id));
    }
}
