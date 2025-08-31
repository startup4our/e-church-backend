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
        $user = User::factory()->create();
        $this->authenticate($user);

        Schedule::factory()->count(2)->create();

        $response = $this->getJson('/api/v1/schedules');

        $response->assertStatus(200)
                 ->assertJsonCount(2);
    }

    public function test_show_returns_schedule()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $schedule = Schedule::factory()->create();

        $response = $this->getJson("/api/v1/schedules/{$schedule->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $schedule->id]);
    }

    public function test_store_creates_schedule()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $creator = User::factory()->create();
        $data = [
            'name' => 'Test Schedule',
            'description' => 'Description',
            'local' => 'Church',
            'date_time' => '2025-09-01 10:00:00',
            'observation' => 'Note',
            'type' => ScheduleType::LOUVOR,
            'aproved' => false,
            'user_creator' => $creator->id
        ];

        $response = $this->postJson('/api/v1/schedules', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['name' => 'Test Schedule']);

        $this->assertDatabaseHas('schedule', ['name' => 'Test Schedule']);
    }

    public function test_update_modifies_schedule()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $schedule = Schedule::factory()->create(['name' => 'Old']);

        $response = $this->putJson("/api/v1/schedules/{$schedule->id}", ['name' => 'Updated']);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Updated']);

        $this->assertDatabaseHas('schedule', ['name' => 'Updated']);
    }

    public function test_destroy_deletes_schedule()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $schedule = Schedule::factory()->create();

        $response = $this->deleteJson("/api/v1/schedules/{$schedule->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('schedule', ['id' => $schedule->id]);
    }
}
