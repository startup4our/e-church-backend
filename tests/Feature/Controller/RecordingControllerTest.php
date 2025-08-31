<?php

namespace Tests\Feature\Http\Controllers;

use App\Enums\RecordingType;
use Tests\TestCase;
use App\Models\Recording;
use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RecordingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_recordings()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        Recording::factory()->count(2)->create();

        $response = $this->getJson('/api/v1/recordings');

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    public function test_show_returns_recording()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $recording = Recording::factory()->create();

        $response = $this->getJson("/api/v1/recordings/{$recording->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $recording->id]);
    }

    public function test_store_creates_recording()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $song = Song::factory()->create();
        $data = [
            'path' => 'http://example.com/recording.mp3',
            'type' => RecordingType::SOLO,
            'description' => 'Test recording',
            'song_id' => $song->id
        ];

        $response = $this->postJson('/api/v1/recordings', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['path' => 'http://example.com/recording.mp3']);

        $this->assertDatabaseHas('recordings', ['path' => 'http://example.com/recording.mp3']);
    }

    public function test_update_modifies_recording()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $recording = Recording::factory()->create(['path' => 'old.mp3']);

        $response = $this->putJson("/api/v1/recordings/{$recording->id}", ['path' => 'updated.mp3']);

        $response->assertStatus(200)
            ->assertJsonFragment(['path' => 'updated.mp3']);

        $this->assertDatabaseHas('recordings', ['path' => 'updated.mp3']);
    }

    public function test_destroy_deletes_recording()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $recording = Recording::factory()->create();

        $response = $this->deleteJson("/api/v1/recordings/{$recording->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('recordings', ['id' => $recording->id]);
    }
}
