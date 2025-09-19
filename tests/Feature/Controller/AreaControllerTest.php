<?php

namespace Tests\Feature\Controller;

use App\Models\Area;
use App\Models\User;
use App\Models\UserArea;
use Tests\TestCase;

class AreaControllerTest extends TestCase
{
    public function test_index_returns_areas_for_authenticated_user()
    {
        $user = $this->createUserWithPermissions(['read_area']);
        $this->authenticate($user);

        $area = Area::factory()->create(['church_id' => $user->church_id]);

        $response = $this->getJson('/api/v1/areas');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         '*' => [
                             'id',
                             'name',
                             'description',
                             'church_id',
                             'created_at',
                             'updated_at'
                         ]
                     ]
                 ])
                 ->assertJson([
                     'success' => true
                 ]);
    }

    public function test_show_returns_area_for_authenticated_user()
    {
        $user = $this->createUserWithPermissions(['read_area']);
        $this->authenticate($user);

        $area = Area::factory()->create(['church_id' => $user->church_id]);

        $response = $this->getJson("/api/v1/areas/{$area->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'id',
                         'name',
                         'description',
                         'church_id',
                         'created_at',
                         'updated_at'
                     ]
                 ])
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'id' => $area->id,
                         'name' => $area->name
                     ]
                 ]);
    }

    public function test_store_creates_area_with_valid_data()
    {
        $user = $this->createUserWithPermissions(['create_area']);
        $this->authenticate($user);

        $areaData = [
            'name' => 'Test Area',
            'description' => 'Test Description'
        ];

        $response = $this->postJson('/api/v1/areas', $areaData);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'id',
                         'name',
                         'description',
                         'church_id',
                         'created_at',
                         'updated_at'
                     ]
                 ])
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'name' => 'Test Area',
                         'description' => 'Test Description',
                         'church_id' => $user->church_id
                     ]
                 ]);

        $this->assertDatabaseHas('area', [
            'name' => 'Test Area',
            'church_id' => $user->church_id
        ]);
    }

    public function test_update_modifies_area_with_valid_data()
    {
        $user = $this->createUserWithPermissions(['update_area']);
        $this->authenticate($user);

        $area = Area::factory()->create(['church_id' => $user->church_id]);

        $updateData = [
            'name' => 'Updated Area Name',
            'description' => 'Updated Description'
        ];

        $response = $this->putJson("/api/v1/areas/{$area->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'id',
                         'name',
                         'description',
                         'church_id',
                         'created_at',
                         'updated_at'
                     ]
                 ])
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         'id' => $area->id,
                         'name' => 'Updated Area Name',
                         'description' => 'Updated Description'
                     ]
                 ]);

        $this->assertDatabaseHas('area', ['name' => 'Updated Area Name']);
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

    public function test_destroy_prevents_deletion_when_area_has_users()
    {
        $user = $this->createUserWithPermissions(['delete_area']);
        $this->authenticate($user);

        $area = Area::factory()->create(['church_id' => $user->church_id]);
        
        // Create a user associated with this area
        $associatedUser = User::factory()->create(['church_id' => $user->church_id]);
        UserArea::create([
            'area_id' => $area->id,
            'user_id' => $associatedUser->id
        ]);

        $response = $this->deleteJson("/api/v1/areas/{$area->id}");

        $response->assertStatus(409)
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
                         'code' => 'AREA_HAS_USERS'
                     ]
                 ]);

        // Verify area still exists
        $this->assertDatabaseHas('area', ['id' => $area->id]);
    }

    public function test_show_returns_error_for_nonexistent_area()
    {
        $user = $this->createUserWithPermissions(['read_area']);
        $this->authenticate($user);

        $response = $this->getJson('/api/v1/areas/99999');

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

    public function test_store_returns_validation_error_for_invalid_data()
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