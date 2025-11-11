<?php

namespace Tests\Unit\Services;

use App\Enums\ScheduleType;
use App\Repositories\ScheduleRepository;
use App\Repositories\ChatRepository;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;
use App\Models\Schedule;
use App\Services\ScheduleService;
use Mockery;

class ScheduleServiceTest extends TestCase
{
    private $repositoryMock;
    private $chatRepositoryMock;
    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(ScheduleRepository::class);
        $this->chatRepositoryMock = Mockery::mock(ChatRepository::class);
        $this->service = new ScheduleService($this->repositoryMock, $this->chatRepositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_schedule()
    {
        $data = [
            'name' => 'Test Schedule',
            'description' => 'Description',
            'local' => 'Church',
            'date_time' => '2025-09-01 10:00:00',
            'observation' => 'Note',
            'type' => ScheduleType::LOUVOR,
            'approved' => false,
            'user_creator' => 1
        ];
        $expected = new Schedule($data);
        $expected->id = 1;

        $this->repositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($expected);

        $this->chatRepositoryMock
            ->shouldReceive('getChatBySchedule')
            ->once()
            ->with(1)
            ->andReturn(null);

        $this->chatRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->andReturn(new \App\Models\Chat());

        $result = $this->service->create($data);

        $this->assertEquals($expected, $result);
    }

    public function test_create_schedule_creates_chat_when_chat_does_not_exist()
    {
        $data = [
            'name' => 'Test Schedule',
            'description' => 'Description',
            'local' => 'Church',
            'start_date' => '2025-09-01 10:00:00',
            'end_date' => '2025-09-01 12:00:00',
            'type' => ScheduleType::LOUVOR,
            'user_creator' => 1
        ];
        
        $schedule = new Schedule($data);
        $schedule->id = 1;
        $chat = new \App\Models\Chat();

        $this->repositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($schedule);

        $this->chatRepositoryMock
            ->shouldReceive('getChatBySchedule')
            ->once()
            ->with(1)
            ->andReturn(null); // Chat doesn't exist

        $this->chatRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($arg) use ($schedule) {
                return $arg['name'] === $schedule->name
                    && $arg['chatable_id'] === $schedule->id
                    && $arg['chatable_type'] === \App\Enums\ChatType::SCALE->value;
            }))
            ->andReturn($chat);

        $result = $this->service->create($data);

        $this->assertEquals($schedule, $result);
    }

    public function test_create_schedule_does_not_create_chat_when_chat_exists()
    {
        $data = [
            'name' => 'Test Schedule',
            'description' => 'Description',
            'local' => 'Church',
            'start_date' => '2025-09-01 10:00:00',
            'end_date' => '2025-09-01 12:00:00',
            'type' => ScheduleType::LOUVOR,
            'user_creator' => 1
        ];
        
        $schedule = new Schedule($data);
        $schedule->id = 1;
        $existingChat = new \App\Models\Chat();

        $this->repositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($schedule);

        $this->chatRepositoryMock
            ->shouldReceive('getChatBySchedule')
            ->once()
            ->with(1)
            ->andReturn($existingChat); // Chat already exists

        $this->chatRepositoryMock
            ->shouldReceive('create')
            ->never(); // Should not create chat

        $result = $this->service->create($data);

        $this->assertEquals($schedule, $result);
    }

    public function test_create_schedule_verifies_chat_has_correct_schedule_association()
    {
        $data = [
            'name' => 'Test Schedule',
            'description' => 'Description',
            'local' => 'Church',
            'start_date' => '2025-09-01 10:00:00',
            'end_date' => '2025-09-01 12:00:00',
            'type' => ScheduleType::LOUVOR,
            'user_creator' => 1
        ];
        
        $schedule = new Schedule($data);
        $schedule->id = 1;
        $chat = new \App\Models\Chat();

        $this->repositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($schedule);

        $this->chatRepositoryMock
            ->shouldReceive('getChatBySchedule')
            ->once()
            ->with(1)
            ->andReturn(null);

        $this->chatRepositoryMock
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($arg) use ($schedule) {
                return $arg['name'] === $schedule->name
                    && $arg['chatable_id'] === $schedule->id
                    && $arg['chatable_type'] === \App\Enums\ChatType::SCALE->value
                    && str_contains($arg['description'], $schedule->name);
            }))
            ->andReturn($chat);

        $result = $this->service->create($data);

        $this->assertEquals($schedule, $result);
    }

    public function test_get_all_schedules()
    {
        $schedules = new Collection([
            new Schedule(['name' => 'A']),
            new Schedule(['name' => 'B'])
        ]);

        $this->repositoryMock
            ->shouldReceive('getAll')
            ->once()
            ->andReturn($schedules);

        $result = $this->service->getAll();

        $this->assertEquals($schedules, $result);
    }

    public function test_get_schedule_by_id()
    {
        $schedule = new Schedule(['name' => 'Test']);

        $this->repositoryMock
            ->shouldReceive('getById')
            ->once()
            ->with(1)
            ->andReturn($schedule);

        $result = $this->service->getById(1);

        $this->assertEquals($schedule, $result);
    }

    public function test_update_schedule()
    {
        $data = ['name' => 'Updated'];
        $schedule = new Schedule(['name' => 'Updated']);

        $this->repositoryMock
            ->shouldReceive('update')
            ->once()
            ->with(1, $data)
            ->andReturn($schedule);

        $result = $this->service->update(1, $data);

        $this->assertEquals($schedule, $result);
    }

    public function test_delete_schedule()
    {
        $this->repositoryMock
            ->shouldReceive('delete')
            ->once()
            ->with(1)
            ->andReturn(true);

        $result = $this->service->delete(1);

        $this->assertTrue($result);
    }
}
