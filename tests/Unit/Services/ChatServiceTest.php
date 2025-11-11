<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Chat;
use App\Models\Message;
use App\Models\UserArea;
use App\Repositories\ChatRepository;
use App\Repositories\MessageRepository;
use App\Repositories\UserAreaRepository;
use App\Services\ChatService;
use App\Services\Interfaces\IStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class ChatServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $service;
    protected $storageServiceMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->storageServiceMock = Mockery::mock(IStorageService::class);
        $this->service = new ChatService(
            new ChatRepository(new Chat()),
            new MessageRepository($this->storageServiceMock),
            new UserAreaRepository(new UserArea()),
            $this->storageServiceMock
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_chat()
    {
        $data = [
            'name' => 'Service Chat',
            'description' => 'Service Description',
            'chatable_id' => 1,
            'chatable_type' => 'App\Models\User'
        ];

        $chat = $this->service->create($data);

        $this->assertDatabaseHas('chat', ['name' => 'Service Chat']);
        $this->assertEquals('Service Chat', $chat->name);
    }
}
