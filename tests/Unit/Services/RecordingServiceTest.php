<?php

namespace Tests\Unit\Services;

use App\Repositories\RecordingRepository;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;
use App\Models\Recording;
use App\Services\RecordingService;
use Mockery;

class RecordingServiceTest extends TestCase
{
    private $repositoryMock;
    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(RecordingRepository::class);
        $this->service = new RecordingService($this->repositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_recording()
    {
        $data = [
            'path' => 'http://example.com/recording.mp3',
            'type' => 'solo',
            'description' => 'Test recording',
            'song_id' => 1
        ];
        $expected = new Recording($data);

        $this->repositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($expected);

        $result = $this->service->create($data);

        $this->assertEquals($expected, $result);
    }

    public function test_get_all_recordings()
    {
        $recordings = new Collection([
            new Recording(['path' => 'A']),
            new Recording(['path' => 'B'])
        ]);

        $this->repositoryMock
            ->shouldReceive('getAll')
            ->once()
            ->andReturn($recordings);

        $result = $this->service->getAll();

        $this->assertEquals($recordings, $result);
    }

    public function test_get_recording_by_id()
    {
        $recording = new Recording(['path' => 'Test']);

        $this->repositoryMock
            ->shouldReceive('getById')
            ->once()
            ->with(1)
            ->andReturn($recording);

        $result = $this->service->getById(1);

        $this->assertEquals($recording, $result);
    }

    public function test_update_recording()
    {
        $data = ['path' => 'updated.mp3'];
        $recording = new Recording(['path' => 'updated.mp3']);

        $this->repositoryMock
            ->shouldReceive('update')
            ->once()
            ->with(1, $data)
            ->andReturn($recording);

        $result = $this->service->update(1, $data);

        $this->assertEquals($recording, $result);
    }

    public function test_delete_recording()
    {
        $this->repositoryMock
            ->shouldReceive('delete')
            ->once()
            ->with(1)
            ->andReturn(true);

        $result = $this->service->delete(1);

        $this->assertTrue($result);
    }
}
