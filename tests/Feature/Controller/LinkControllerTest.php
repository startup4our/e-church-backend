<?php

namespace Tests\Feature\Http\Controllers;

use Tests\TestCase;
use App\Models\Link;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LinkControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_links()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        Link::factory()->count(2)->create();

        $response = $this->getJson('/api/v1/links');

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    public function test_show_returns_link()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $link = Link::factory()->create();

        $response = $this->getJson("/api/v1/links/{$link->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $link->id]);
    }

    public function test_show_returns_404_for_nonexistent_link()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $response = $this->getJson('/api/v1/links/999');

        $response->assertStatus(404);
    }

    public function test_store_creates_link()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $song = Song::factory()->create();
        $data = [
            'name' => 'Test Link',
            'destination' => 'https://example.com',
            'description' => 'A test link',
            'song_id' => $song->id
        ];

        $response = $this->postJson('/api/v1/links', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'Test Link']);

        $this->assertDatabaseHas('links', ['name' => 'Test Link']);
    }

    public function test_store_fails_with_invalid_data()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $data = [
            'name' => '',
            'destination' => 'not-a-url',
            'song_id' => 999 // non-existent song
        ];

        $response = $this->postJson('/api/v1/links', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'destination', 'song_id']);
    }

    public function test_update_modifies_link()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $link = Link::factory()->create(['name' => 'Old Name']);

        $response = $this->putJson("/api/v1/links/{$link->id}", ['name' => 'Updated Name']);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Name']);

        $this->assertDatabaseHas('links', ['name' => 'Updated Name']);
    }

    public function test_update_fails_with_invalid_data()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $link = Link::factory()->create();

        $response = $this->putJson("/api/v1/links/{$link->id}", [
            'destination' => 'not-a-url'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['destination']);
    }

    public function test_update_returns_404_for_nonexistent_link()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $response = $this->putJson('/api/v1/links/999', ['name' => 'Test']);

        $response->assertStatus(404);
    }

    public function test_destroy_deletes_link()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $link = Link::factory()->create();

        $response = $this->deleteJson("/api/v1/links/{$link->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('links', ['id' => $link->id]);
    }

    public function test_destroy_returns_404_for_nonexistent_link()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $response = $this->deleteJson('/api/v1/links/999');

        $response->assertStatus(404);
    }
}
