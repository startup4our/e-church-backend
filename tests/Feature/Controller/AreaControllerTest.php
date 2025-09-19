<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;
use App\Models\Area;
use App\Models\User;
use App\Models\Church;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AreaControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_areas()
    {
        $user = $this->createUserWithPermissions(['read_area']);
        $this->authenticate($user);

        Area::factory()->count(2)->create(['church_id' => $user->church_id]);

        $response = $this->getJson('/api/v1/areas');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         '*' => ['id', 'name', 'description', 'church_id']
                     ]
                 ])
                 ->assertJsonCount(2, 'data');
    }

    public function test_show_returns_area()
    {
        $user = $this->createUserWithPermissions(['read_area']);
        $this->authenticate($user);

        $area = Area::factory()->create(['church_id' => $user->church_id]);

        $response = $this->getJson("/api/v1/areas/{$area->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => ['id', 'name', 'description', 'church_id']
                 ])
                 ->assertJsonFragment(['id' => $area->id]);
    }

    public function test_store_creates_area()
    {
        $user = $this->createUserWithPermissions(['create_area']);
        $this->authenticate($user);

        $data = ['name' => 'New Area', 'description' => 'Testing'];

        $response = $this->postJson('/api/v1/areas', $data);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'data' => ['id', 'name', 'description', 'church_id']
                 ])
                 ->assertJsonFragment(['name' => 'New Area']);

        $this->assertDatabaseHas('area', ['name' => 'New Area']);
    }

    public function test_update_modifies_area()
    {
        $user = $this->createUserWithPermissions(['update_area']);
        $this->authenticate($user);

        $area = Area::factory()->create(['name' => 'Old', 'church_id' => $user->church_id]);

        $response = $this->putJson("/api/v1/areas/{$area->id}", ['name' => 'Updated']);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => ['id', 'name', 'description', 'church_id']
                 ])
                 ->assertJsonFragment(['name' => 'Updated']);

        $this->assertDatabaseHas('area', ['name' => 'Updated']);
    }

    public function test_destroy_deletes_area()
    {
        $user = $this->createUserWithPermissions(['delete_area']);
        $this->authenticate($user);

        $area = Area::factory()->create(['church_id' => $user->church_id]);

        $response = $this->deleteJson("/api/v1/areas/{$area->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('area', ['id' => $area->id]);
    }

    public function test_show_returns_error_for_nonexistent_area()
    {
        $user = $this->createUserWithPermissions(['read_area']);
        $this->authenticate($user);

        $response = $this->getJson('/api/v1/areas/999');

        $response->assertStatus(404)
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
                         'code' => 'AREA_NOT_FOUND'
                     ]
                 ]);
    }

    public function test_store_returns_validation_error()
    {
        $user = $this->createUserWithPermissions(['create_area']);
        $this->authenticate($user);

        $response = $this->postJson('/api/v1/areas', []);

        $response->assertStatus(422)
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
                         'code' => 'VALIDATION_ERROR'
                     ]
                 ]);
    }
}
