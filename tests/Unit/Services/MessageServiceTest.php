<?php

namespace Tests\Unit;

use App\Models\Message;
use App\Repositories\MessageRepository;
use App\Services\MessageService;
use Tests\TestCase;
use Mockery;

class MessageServiceTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_message()
    {
        $repo = Mockery::mock(MessageRepository::class);
        $repo->shouldReceive('create')->once()->andReturn(new Message(['id' => 1]));

        $service = new MessageService($repo);

        $data = ['content' => 'Hello', 'sent_at' => now(), 'chat_id' => 1, 'user_id' => 1];
        $message = $service->create($data);

        $this->assertInstanceOf(Message::class, $message);
    }
}
