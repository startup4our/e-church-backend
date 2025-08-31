<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Song;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SongControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_songs()
    {
        $user = User::factory()->create();
        $this->authenticate($user); // header Bearer JWT

        Song::factory()->count(2)->create();

        $response = $this->getJson('/api/v1/songs');

        $response->assertStatus(200)
                 ->assertJsonCount(2, 'data'); // se usar paginate
    }

    public function test_show_returns_song()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $song = Song::factory()->create();

        $response = $this->getJson("/api/v1/songs/{$song->id}");

        $response->assertStatus(200)
                 ->assertJsonFragment(['id' => $song->id]);
    }

    public function test_store_creates_song()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $data = [
            'cover_path'  => 'https://example.com/covers/abc.jpg',
            'name'        => 'Nova Música',
            'artist'      => 'Artista XPTO',
            'duration'    => 180,
            'album'       => 'Álbum Legal',
            'spotify_id'  => 'sp_123',
            'preview_url' => 'https://example.com/prev.mp3',
            'spotify_url' => 'https://open.spotify.com/track/xyz',
        ];

        $response = $this->postJson('/api/v1/songs', $data);

        $response->assertStatus(201)
                 ->assertJsonFragment(['name' => 'Nova Música']);

        $this->assertDatabaseHas('song', ['name' => 'Nova Música', 'artist' => 'Artista XPTO']);
    }

    public function test_update_modifies_song()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $song = Song::factory()->create(['name' => 'Antiga', 'duration' => 120]);

        $response = $this->putJson("/api/v1/songs/{$song->id}", [
            'name' => 'Atualizada',
            'duration' => 200,
        ]);

        $response->assertStatus(200)
                 ->assertJsonFragment(['name' => 'Atualizada']);

        $this->assertDatabaseHas('song', ['id' => $song->id, 'name' => 'Atualizada', 'duration' => 200]);
    }

    public function test_destroy_deletes_song()
    {
        $user = User::factory()->create();
        $this->authenticate($user);

        $song = Song::factory()->create();

        $response = $this->deleteJson("/api/v1/songs/{$song->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('song', ['id' => $song->id]);
    }
}
