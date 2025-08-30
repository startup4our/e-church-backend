<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Chat;
use App\Repositories\ChatRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChatRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ChatRepository(new Chat());
    }

    public function test_create_chat()
    {
        $data = [
            'name' => 'Chat Test',
            'description' => 'Description Test',
            'chatable_id' => 1,
            'chatable_type' => 'App\Models\User'
        ];

        $chat = $this->repository->create($data);

        $this->assertDatabaseHas('chat', ['name' => 'Chat Test']);
        $this->assertEquals('Chat Test', $chat->name);
    }

    public function test_get_all_chats()
    {
        Chat::factory()->count(3)->create();

        $chats = $this->repository->getAll();

        $this->assertCount(3, $chats);
    }

    public function test_update_chat()
    {
        $chat = Chat::factory()->create();

        $updated = $this->repository->update($chat->id, ['name' => 'Updated']);

        $this->assertEquals('Updated', $updated->name);
    }

    public function test_delete_chat()
    {
        $chat = Chat::factory()->create();

        $deleted = $this->repository->delete($chat->id);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('chat', ['id' => $chat->id]);
    }
}
