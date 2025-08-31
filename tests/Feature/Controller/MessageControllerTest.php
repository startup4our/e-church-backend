<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\User;
use App\Models\Chat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_message()
    {
        $user = User::factory()->create();
        $chat = Chat::factory()->create();

        // autentica o usuário e adiciona o header Authorization: Bearer
        $this->authenticate($user);

        $response = $this->postJson('/api/v1/message', [
            'content' => 'Hello world',
            'sent_at' => now()->toDateTimeString(),
            'chat_id' => $chat->id,
            'user_id' => $user->id,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('messages', [
            'content' => 'Hello world',
            'chat_id' => $chat->id,
            'user_id' => $user->id,
        ]);
    }
}
