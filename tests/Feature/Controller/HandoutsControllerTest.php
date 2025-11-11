<?php

namespace Tests\Feature\Controller;

use App\Models\Handout;
use App\Models\Church;
use App\Models\User;
use App\Models\Area;
use App\Models\UserArea;
use App\Enums\HandoutStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HandoutsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_handouts_for_church()
    {
        $user = $this->createUserWithPermissions(['manage_handouts']);
        $this->authenticate($user);

        Handout::factory()->count(3)->create(['church_id' => $user->church_id]);
        Handout::factory()->create(['church_id' => Church::factory()->create()->id]); // Different church

        $response = $this->getJson('/api/v1/handouts');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         '*' => ['id', 'title', 'description', 'status', 'church_id']
                     ]
                 ])
                 ->assertJsonCount(3, 'data');
    }

    public function test_index_requires_manage_handouts_permission()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $response = $this->getJson('/api/v1/handouts');

        $response->assertStatus(403)
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
                         'code' => 'FORBIDDEN'
                     ]
                 ]);
    }

    public function test_store_creates_handout_with_valid_data()
    {
        $user = $this->createUserWithPermissions(['manage_handouts']);
        $this->authenticate($user);

        $data = [
            'title' => 'Test Handout',
            'description' => 'Test Description',
            'priority' => 'high',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
        ];

        $response = $this->postJson('/api/v1/handouts', $data);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => ['id', 'title', 'description', 'status', 'church_id']
                 ])
                 ->assertJsonFragment(['title' => 'Test Handout']);

        $this->assertDatabaseHas('handouts', [
            'title' => 'Test Handout',
            'church_id' => $user->church_id,
            'status' => HandoutStatus::PENDING->value
        ]);
    }

    public function test_store_creates_active_handout_when_activate_is_true()
    {
        $user = $this->createUserWithPermissions(['manage_handouts']);
        $this->authenticate($user);

        $data = [
            'title' => 'Active Handout',
            'description' => 'Description',
            'activate' => true,
        ];

        $response = $this->postJson('/api/v1/handouts', $data);

        $response->assertStatus(200);

        $this->assertDatabaseHas('handouts', [
            'title' => 'Active Handout',
            'status' => HandoutStatus::ACTIVE->value
        ]);
    }

    public function test_store_creates_handout_with_area()
    {
        $user = $this->createUserWithPermissions(['manage_handouts']);
        $this->authenticate($user);
        $area = Area::factory()->create(['church_id' => $user->church_id]);

        $data = [
            'title' => 'Area Handout',
            'description' => 'Description',
            'area_id' => $area->id,
        ];

        $response = $this->postJson('/api/v1/handouts', $data);

        $response->assertStatus(200);

        $this->assertDatabaseHas('handouts', [
            'title' => 'Area Handout',
            'area_id' => $area->id
        ]);
    }

    public function test_update_modifies_handout()
    {
        $user = $this->createUserWithPermissions(['manage_handouts']);
        $this->authenticate($user);

        $handout = Handout::factory()->create([
            'church_id' => $user->church_id,
            'title' => 'Old Title'
        ]);

        $response = $this->putJson("/api/v1/handouts/{$handout->id}", [
            'title' => 'Updated Title',
            'status' => HandoutStatus::ACTIVE->value
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'Updated Title']);

        $this->assertDatabaseHas('handouts', [
            'id' => $handout->id,
            'title' => 'Updated Title',
            'status' => HandoutStatus::ACTIVE->value
        ]);
    }

    public function test_update_prevents_updating_handout_from_different_church()
    {
        $user = $this->createUserWithPermissions(['manage_handouts']);
        $this->authenticate($user);

        $otherChurch = Church::factory()->create();
        $handout = Handout::factory()->create(['church_id' => $otherChurch->id]);

        $response = $this->putJson("/api/v1/handouts/{$handout->id}", [
            'title' => 'Hacked Title'
        ]);

        $response->assertStatus(403)
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
                         'code' => 'FORBIDDEN'
                     ]
                 ]);
    }

    public function test_destroy_inactivates_handout()
    {
        $user = $this->createUserWithPermissions(['manage_handouts']);
        $this->authenticate($user);

        $handout = Handout::factory()->create([
            'church_id' => $user->church_id,
            'status' => HandoutStatus::ACTIVE->value
        ]);

        $response = $this->deleteJson("/api/v1/handouts/{$handout->id}");

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('handouts', [
            'id' => $handout->id,
            'status' => HandoutStatus::DELETED->value
        ]);
    }

    public function test_active_returns_only_visible_handouts()
    {
        $user = $this->createUserWithPermissions(['manage_handouts']);
        $this->authenticate($user);

        $area = Area::factory()->create(['church_id' => $user->church_id]);
        UserArea::create([
            'user_id' => $user->id,
            'area_id' => $area->id
        ]);

        // Create active handout visible now
        Handout::factory()->create([
            'church_id' => $user->church_id,
            'status' => HandoutStatus::ACTIVE->value,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'area_id' => $area->id,
        ]);

        // Create inactive handout
        Handout::factory()->create([
            'church_id' => $user->church_id,
            'status' => HandoutStatus::INACTIVE->value,
        ]);

        $response = $this->getJson('/api/v1/handouts/active');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         '*' => ['id', 'title', 'status']
                     ]
                 ]);
    }

    public function test_store_returns_validation_error_for_invalid_data()
    {
        $user = $this->createUserWithPermissions(['manage_handouts']);
        $this->authenticate($user);

        $response = $this->postJson('/api/v1/handouts', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['title', 'description']);
    }

    public function test_update_handles_handout_not_found_error()
    {
        $user = $this->createUserWithPermissions(['manage_handouts']);
        $this->authenticate($user);

        $response = $this->putJson('/api/v1/handouts/99999', [
            'title' => 'Updated Title'
        ]);

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
                         'code' => 'HANDOUT_NOT_FOUND'
                     ]
                 ]);
    }

    public function test_destroy_handles_handout_not_found_error()
    {
        $user = $this->createUserWithPermissions(['manage_handouts']);
        $this->authenticate($user);

        $response = $this->deleteJson('/api/v1/handouts/99999');

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
                         'code' => 'HANDOUT_NOT_FOUND'
                     ]
                 ]);
    }
}

