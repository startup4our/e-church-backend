<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Area;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_roles()
    {
        Role::factory()->count(2)->create();

        $response = $this->getJson('/api/v1/roles');

        $response->assertStatus(200)
                 ->assertJsonCount(2);
    }

    public function test_show_returns_role()
    {
        $role = Role::factory()->create();

        $response = $this->getJson("/api/v1/roles/{$role->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $role->id]);
    }

    public function test_store_creates_role()
    {
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
        $role = Role::factory()->create();

        $response = $this->deleteJson("/api/v1/roles/{$role->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('role', ['id' => $role->id]);
    }
}
