<?php

namespace Tests\Unit;

use App\Models\Message;
use App\Models\User;
use App\Models\Chat;
use App\Repositories\MessageRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected MessageRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new MessageRepository();
    }

    public function test_create_and_find_by_id()
    {
        $user = User::factory()->create();
        $chat = Chat::factory()->create();

        $data = [
            'content' => 'Hello',
            'sent_at' => now(),
            'chat_id' => $chat->id,
            'user_id' => $user->id,
        ];

        $created = $this->repository->create($data);

        $this->assertDatabaseHas('messages', $data);

        $found = $this->repository->findById($created->id);

        $this->assertNotNull($found);
        $this->assertEquals($created->id, $found->id);
        $this->assertEquals($user->id, $found->user_id);
        $this->assertEquals($chat->id, $found->chat_id);
    }

    public function test_update_changes_fields()
    {
        $message = Message::factory()->create(['content' => 'Old content']);

        $updated = $this->repository->update($message, ['content' => 'New content']);

        $this->assertEquals('New content', $updated->content);
        $this->assertDatabaseHas('messages', ['id' => $message->id, 'content' => 'New content']);
    }

    public function test_delete_removes_record()
    {
        $message = Message::factory()->create();

        $this->repository->delete($message);

        $this->assertDatabaseMissing('messages', ['id' => $message->id]);
    }

    public function test_all_returns_collection()
    {
        Message::factory()->count(3)->create();

        $all = $this->repository->all();

        $this->assertCount(3, $all);
        $this->assertInstanceOf(Message::class, $all->first());
    }
}
