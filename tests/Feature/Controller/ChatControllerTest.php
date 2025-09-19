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
        $user = $this->createUserWithPermissions(['create_chat']);
        $this->authenticate($user);

        $response = $this->postJson('/api/v1/chats', [
            'name' => 'New Chat',
            'description' => 'Some description',
            'chatable_id' => $user->id,
            'chatable_type' => 'I', // ChatType::INDEPENDENT
            'user_creator' => $user->id
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'data' => ['id', 'name', 'description', 'chatable_id', 'chatable_type']
                 ])
                 ->assertJsonFragment(['name' => 'New Chat']);
    }

    public function test_index_chats()
    {
        $user = $this->createUserWithPermissions(['read_chat']);
        $this->authenticate($user);

        Chat::factory()->count(2)->create();

        $response = $this->getJson('/api/v1/chats');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         '*' => ['id', 'name', 'description', 'chatable_id', 'chatable_type']
                     ]
                 ])
                 ->assertJsonCount(2, 'data');
    }

    public function test_show_chat()
    {
        $user = $this->createUserWithPermissions(['read_chat']);
        $this->authenticate($user);

        $chat = Chat::factory()->create();

        $response = $this->getJson("/api/v1/chats/{$chat->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => ['id', 'name', 'description', 'chatable_id', 'chatable_type']
                 ])
                 ->assertJsonFragment(['id' => $chat->id]);
    }

    public function test_update_chat()
    {
        $user = $this->createUserWithPermissions(['update_chat']);
        $this->authenticate($user);

        $chat = Chat::factory()->create();

        $response = $this->putJson("/api/v1/chats/{$chat->id}", [
            'name' => 'Updated Name',
            'user_updater' => $user->id
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => ['id', 'name', 'description', 'chatable_id', 'chatable_type']
                 ])
                 ->assertJsonFragment(['name' => 'Updated Name']);
    }

    public function test_delete_chat()
    {
        $user = $this->createUserWithPermissions(['delete_chat']);
        $this->authenticate($user);

        $chat = Chat::factory()->create();

        $response = $this->deleteJson("/api/v1/chats/{$chat->id}", [
            'user_id' => $user->id
        ]);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('chat', ['id' => $chat->id]);
    }
}
