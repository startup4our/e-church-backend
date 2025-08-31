<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChatControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_chat()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $response = $this->postJson('/api/v1/chats', [
            'name' => 'New Chat',
            'description' => 'Some description',
            'chatable_id' => 1,
            'chatable_type' => 'App\Models\User'
        ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['name' => 'New Chat']);
    }

    public function test_index_chats()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        Chat::factory()->count(2)->create();

        $response = $this->getJson('/api/v1/chats');

        $response->assertStatus(200)
                 ->assertJsonCount(2);
    }

    public function test_show_chat()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $chat = Chat::factory()->create();

        $response = $this->getJson("/api/v1/chats/{$chat->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $chat->id]);
    }

    public function test_update_chat()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $chat = Chat::factory()->create();

        $response = $this->putJson("/api/v1/chats/{$chat->id}", [
            'name' => 'Updated Name'
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Updated Name']);
    }

    public function test_delete_chat()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $chat = Chat::factory()->create();

        $response = $this->deleteJson("/api/v1/chats/{$chat->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('chat', ['id' => $chat->id]);
    }
}
