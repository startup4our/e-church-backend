<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;
use App\Models\Area;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AreaControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_areas()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        Area::factory()->count(2)->create();

        $response = $this->getJson('/api/v1/areas');

        $response->assertStatus(200)
                 ->assertJsonCount(2);
    }

    public function test_show_returns_area()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $area = Area::factory()->create();

        $response = $this->getJson("/api/v1/areas/{$area->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $area->id]);
    }

    public function test_store_creates_area()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $data = ['name' => 'New Area', 'description' => 'Testing'];

        $response = $this->postJson('/api/v1/areas', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['name' => 'New Area']);

        $this->assertDatabaseHas('area', ['name' => 'New Area']);
    }

    public function test_update_modifies_area()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $area = Area::factory()->create(['name' => 'Old']);

        $response = $this->putJson("/api/v1/areas/{$area->id}", ['name' => 'Updated']);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Updated']);

        $this->assertDatabaseHas('area', ['name' => 'Updated']);
    }

    public function test_destroy_deletes_area()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $area = Area::factory()->create();

        $response = $this->deleteJson("/api/v1/areas/{$area->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('area', ['id' => $area->id]);
    }
}
