<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Chat;
use App\Repositories\ChatRepository;
use App\Services\ChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChatServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ChatService(new ChatRepository(new Chat()));
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
