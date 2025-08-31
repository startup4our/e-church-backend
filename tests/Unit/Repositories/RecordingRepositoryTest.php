<?php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use App\Models\Recording;
use App\Models\Song;
use App\Repositories\RecordingRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RecordingRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new RecordingRepository(new Recording());
    }

    public function test_create_recording()
    {
        $song = Song::factory()->create();
        $data = [
            'path' => 'http://example.com/recording.mp3',
            'type' => 'solo',
            'description' => 'Test recording',
            'song_id' => $song->id
        ];

        $recording = $this->repository->create($data);

        $this->assertDatabaseHas('recordings', ['path' => 'http://example.com/recording.mp3']);
        $this->assertEquals('http://example.com/recording.mp3', $recording->path);
    }

    public function test_get_all_recordings()
    {
        Recording::factory()->count(3)->create();

        $recordings = $this->repository->getAll();

        $this->assertCount(3, $recordings);
    }

    public function test_get_recording_by_id()
    {
        $recording = Recording::factory()->create();

        $found = $this->repository->getById($recording->id);

        $this->assertEquals($recording->id, $found->id);
    }

    public function test_update_recording()
    {
        $recording = Recording::factory()->create();

        $updated = $this->repository->update($recording->id, ['path' => 'updated.mp3']);

        $this->assertEquals('updated.mp3', $updated->path);
        $this->assertDatabaseHas('recordings', ['path' => 'updated.mp3']);
    }

    public function test_delete_recording()
    {
        $recording = Recording::factory()->create();

        $result = $this->repository->delete($recording->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('recordings', ['id' => $recording->id]);
    }
}
