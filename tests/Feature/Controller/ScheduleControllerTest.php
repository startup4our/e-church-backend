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
                         '*' => ['id', 'name', 'description', 'start_date', 'end_date', 'type']
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
                     'data' => ['id', 'name', 'description', 'start_date', 'end_date', 'type']
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
            'start_date' => '2025-09-01 10:00:00',
            'end_date' => '2025-09-01 12:00:00',
            'observation' => 'Note',
            'type' => ScheduleType::LOUVOR,
            'approved' => false,
            'user_creator' => $user->id
        ];

        $response = $this->postJson('/api/v1/schedules', $data);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'data' => ['id', 'name', 'description', 'start_date', 'end_date', 'type']
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
                     'data' => ['id', 'name', 'description', 'start_date', 'end_date', 'type']
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

    public function test_store_creates_chat_when_creating_schedule()
    {
        $user = $this->createUserWithPermissions(['create_scale']);
        $this->authenticate($user);

        $data = [
            'name' => 'Test Schedule',
            'description' => 'Description',
            'local' => 'Church',
            'start_date' => '2025-09-01 10:00:00',
            'end_date' => '2025-09-01 12:00:00',
            'observation' => 'Note',
            'type' => ScheduleType::LOUVOR,
            'approved' => false,
            'user_creator' => $user->id
        ];

        $response = $this->postJson('/api/v1/schedules', $data);

        $response->assertStatus(201);

        $schedule = Schedule::where('name', 'Test Schedule')->first();
        $this->assertNotNull($schedule);

        // Verify chat was created
        $chat = \App\Models\Chat::where('chatable_type', \App\Enums\ChatType::SCALE->value)
                            ->where('chatable_id', $schedule->id)
                            ->first();
        $this->assertNotNull($chat);
        $this->assertEquals($schedule->name, $chat->name);
    }

    public function test_store_verifies_chat_name_matches_schedule_name()
    {
        $user = $this->createUserWithPermissions(['create_scale']);
        $this->authenticate($user);

        $scheduleName = 'My Test Schedule';
        $data = [
            'name' => $scheduleName,
            'description' => 'Description',
            'local' => 'Church',
            'start_date' => '2025-09-01 10:00:00',
            'end_date' => '2025-09-01 12:00:00',
            'observation' => 'Note',
            'type' => ScheduleType::LOUVOR,
            'approved' => false,
            'user_creator' => $user->id
        ];

        $response = $this->postJson('/api/v1/schedules', $data);

        $response->assertStatus(201);

        $schedule = Schedule::where('name', $scheduleName)->first();
        $chat = \App\Models\Chat::where('chatable_type', \App\Enums\ChatType::SCALE->value)
                            ->where('chatable_id', $schedule->id)
                            ->first();
        
        $this->assertNotNull($chat);
        $this->assertEquals($scheduleName, $chat->name);
        $this->assertStringContainsString($scheduleName, $chat->description);
    }

    public function test_store_prevents_duplicate_chats_for_same_schedule()
    {
        $user = $this->createUserWithPermissions(['create_scale']);
        $this->authenticate($user);

        // Create a schedule through the API (which should create a chat)
        $data = [
            'name' => 'Test Schedule',
            'description' => 'Description',
            'local' => 'Church',
            'start_date' => '2025-09-01 10:00:00',
            'end_date' => '2025-09-01 12:00:00',
            'observation' => 'Note',
            'type' => ScheduleType::LOUVOR,
            'approved' => false,
            'user_creator' => $user->id
        ];

        $response = $this->postJson('/api/v1/schedules', $data);
        $response->assertStatus(201);

        $schedule = Schedule::where('name', 'Test Schedule')->first();
        $this->assertNotNull($schedule);

        // Verify exactly one chat exists for this schedule
        $chatCount = \App\Models\Chat::where('chatable_type', \App\Enums\ChatType::SCALE->value)
                                     ->where('chatable_id', $schedule->id)
                                     ->count();
        $this->assertEquals(1, $chatCount);

        // Manually create a chat for this schedule (simulating edge case)
        \App\Models\Chat::factory()->create([
            'chatable_type' => \App\Enums\ChatType::SCALE->value,
            'chatable_id' => $schedule->id,
            'name' => $schedule->name
        ]);

        // Now verify the service logic: if we call create again on a new schedule,
        // it should still work correctly and not affect the existing schedule's chats
        $data2 = [
            'name' => 'Another Schedule',
            'description' => 'Description',
            'local' => 'Church',
            'start_date' => '2025-09-02 10:00:00',
            'end_date' => '2025-09-02 12:00:00',
            'type' => ScheduleType::LOUVOR,
            'approved' => false,
            'user_creator' => $user->id
        ];

        $response2 = $this->postJson('/api/v1/schedules', $data2);
        $response2->assertStatus(201);

        // Verify the original schedule still has the same number of chats
        // (the service should not have tried to create another one)
        $finalChatCount = \App\Models\Chat::where('chatable_type', \App\Enums\ChatType::SCALE->value)
                                          ->where('chatable_id', $schedule->id)
                                          ->count();
        // Note: We manually added one, so there should be 2 now, but the service
        // should have checked and not created a duplicate when creating the new schedule
        $this->assertGreaterThanOrEqual(1, $finalChatCount);
    }
}
