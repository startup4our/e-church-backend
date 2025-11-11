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

        // Create an area for the user's church
        $area = \App\Models\Area::factory()->create(['church_id' => $user->church_id]);
        
        // Add user to the area so they have access
        \App\Models\UserArea::create([
            'user_id' => $user->id,
            'area_id' => $area->id
        ]);

        $response = $this->postJson('/api/v1/chats', [
            'name' => 'New Chat',
            'description' => 'Some description',
            'chatable_id' => $area->id,
            'chatable_type' => \App\Enums\ChatType::AREA->value, // 'A' - Laravel Enum validation accepts the value
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

        // Create chats associated with areas
        $area1 = \App\Models\Area::factory()->create(['church_id' => $user->church_id]);
        $area2 = \App\Models\Area::factory()->create(['church_id' => $user->church_id]);
        
        Chat::factory()->create([
            'chatable_id' => $area1->id,
            'chatable_type' => \App\Enums\ChatType::AREA->value
        ]);
        Chat::factory()->create([
            'chatable_id' => $area2->id,
            'chatable_type' => \App\Enums\ChatType::AREA->value
        ]);

        $response = $this->getJson('/api/v1/chats');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         '*' => ['id', 'name', 'description', 'chatable_id', 'chatable_type']
                     ]
                 ]);
        
        // Just verify we get at least 2 chats back
        $responseData = $response->json('data');
        $this->assertGreaterThanOrEqual(2, count($responseData));
    }

    public function test_show_chat()
    {
        $user = $this->createUserWithPermissions(['read_chat']);
        $this->authenticate($user);

        // Create an area for the user's church
        $area = \App\Models\Area::factory()->create(['church_id' => $user->church_id]);
        
        // Add user to the area so they have access
        \App\Models\UserArea::create([
            'user_id' => $user->id,
            'area_id' => $area->id
        ]);

        // Create a chat associated with the area
        $chat = Chat::factory()->create([
            'chatable_id' => $area->id,
            'chatable_type' => \App\Enums\ChatType::AREA->value
        ]);

        $response = $this->getJson("/api/v1/chats/{$chat->id}?user_id={$user->id}");

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

        // Create an area for the user's church
        $area = \App\Models\Area::factory()->create(['church_id' => $user->church_id]);
        
        // Add user to the area so they have access
        \App\Models\UserArea::create([
            'user_id' => $user->id,
            'area_id' => $area->id
        ]);

        // Create a chat associated with the area
        $chat = Chat::factory()->create([
            'chatable_id' => $area->id,
            'chatable_type' => \App\Enums\ChatType::AREA->value
        ]);

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

        // Create an area for the user's church
        $area = \App\Models\Area::factory()->create(['church_id' => $user->church_id]);
        
        // Add user to the area so they have access
        \App\Models\UserArea::create([
            'user_id' => $user->id,
            'area_id' => $area->id
        ]);

        // Create a chat associated with the area
        $chat = Chat::factory()->create([
            'chatable_id' => $area->id,
            'chatable_type' => \App\Enums\ChatType::AREA->value
        ]);

        $response = $this->deleteJson("/api/v1/chats/{$chat->id}", [
            'user_id' => $user->id
        ]);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('chat', ['id' => $chat->id]);
    }
}
