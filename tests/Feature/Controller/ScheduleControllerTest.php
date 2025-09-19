<?php

namespace Tests\Feature\Http\Controllers;

use App\Enums\ScheduleType;
use Tests\TestCase;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScheduleControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_schedules()
    {
        $user = $this->createUserWithPermissions(['read_scale']);
        $this->authenticate($user);

        Schedule::factory()->count(2)->create();

        $response = $this->getJson('/api/v1/schedules');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         '*' => ['id', 'name', 'description', 'date_time', 'type']
                     ]
                 ])
                 ->assertJsonCount(2, 'data');
    }

    public function test_show_returns_schedule()
    {
        $user = $this->createUserWithPermissions(['read_scale']);
        $this->authenticate($user);

        $schedule = Schedule::factory()->create();

        $response = $this->getJson("/api/v1/schedules/{$schedule->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => ['id', 'name', 'description', 'date_time', 'type']
                 ])
                 ->assertJsonFragment(['id' => $schedule->id]);
    }

    public function test_store_creates_schedule()
    {
        $user = $this->createUserWithPermissions(['create_scale']);
        $this->authenticate($user);

        $data = [
            'name' => 'Test Schedule',
            'description' => 'Description',
            'local' => 'Church',
            'date_time' => '2025-09-01 10:00:00',
            'observation' => 'Note',
            'type' => ScheduleType::LOUVOR,
            'approved' => false,
            'user_creator' => $user->id
        ];

        $response = $this->postJson('/api/v1/schedules', $data);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'data' => ['id', 'name', 'description', 'date_time', 'type']
                 ])
                 ->assertJsonFragment(['name' => 'Test Schedule']);

        $this->assertDatabaseHas('schedule', ['name' => 'Test Schedule']);
    }

    public function test_update_modifies_schedule()
    {
        $user = $this->createUserWithPermissions(['update_scale']);
        $this->authenticate($user);

        $schedule = Schedule::factory()->create(['name' => 'Old']);

        $response = $this->putJson("/api/v1/schedules/{$schedule->id}", ['name' => 'Updated']);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => ['id', 'name', 'description', 'date_time', 'type']
                 ])
                 ->assertJsonFragment(['name' => 'Updated']);

        $this->assertDatabaseHas('schedule', ['name' => 'Updated']);
    }

    public function test_destroy_deletes_schedule()
    {
        $user = $this->createUserWithPermissions(['delete_scale']);
        $this->authenticate($user);

        $schedule = Schedule::factory()->create();

        $response = $this->deleteJson("/api/v1/schedules/{$schedule->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('schedule', ['id' => $schedule->id]);
    }
}
