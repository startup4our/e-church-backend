<?php

namespace Tests\Unit\Repositories;

use App\Enums\ScheduleType;
use Tests\TestCase;
use App\Models\Schedule;
use App\Models\User;
use App\Repositories\ScheduleRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ScheduleRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ScheduleRepository(new Schedule());
    }

    public function test_create_schedule()
    {
        $user = User::factory()->create();
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

        $schedule = $this->repository->create($data);

        $this->assertDatabaseHas('schedule', ['name' => 'Test Schedule']);
        $this->assertEquals('Test Schedule', $schedule->name);
    }

    public function test_get_all_schedules()
    {
        Schedule::factory()->count(3)->create();

        $schedules = $this->repository->getAll();

        $this->assertCount(3, $schedules);
    }

    public function test_get_schedule_by_id()
    {
        $schedule = Schedule::factory()->create();

        $found = $this->repository->getById($schedule->id);

        $this->assertEquals($schedule->id, $found->id);
    }

    public function test_update_schedule()
    {
        $schedule = Schedule::factory()->create();

        $updated = $this->repository->update($schedule->id, ['name' => 'Updated']);

        $this->assertEquals('Updated', $updated->name);
        $this->assertDatabaseHas('schedule', ['name' => 'Updated']);
    }

    public function test_delete_schedule()
    {
        $schedule = Schedule::factory()->create();

        $result = $this->repository->delete($schedule->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('schedule', ['id' => $schedule->id]);
    }
}
