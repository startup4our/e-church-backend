<?php

namespace Tests\Unit\Services;

use App\Models\Song;
use App\Repositories\SongRepository;
use App\Services\SongService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Tests\TestCase;

class SongServiceTest extends TestCase
{
    private $repo;
    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = Mockery::mock(SongRepository::class);
        $this->service = new SongService($this->repo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_song()
    {
        $data = ['name' => 'Nova Música', 'artist' => 'A', 'duration' => 180];

        // se sua service checa duplicidade por spotify_id, simule que não existe
        $this->repo->shouldReceive('existsSpotifyId')
            ->zeroOrMoreTimes()
            ->andReturnFalse();

        $expected = new Song($data);

        $this->repo->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($expected);

        $result = $this->service->create($data);

        $this->assertInstanceOf(Song::class, $result);
        $this->assertEquals('Nova Música', $result->name);
    }

    public function test_list_returns_paginated_songs()
    {
        // você pode mockar a interface de paginação se quiser;
        // aqui simplifico retornando um mock que implementa LengthAwarePaginator
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $this->repo->shouldReceive('paginate')
            ->once()
            ->with(null, 15)
            ->andReturn($paginator);

        $result = $this->service->list();

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }

    public function test_get_returns_song()
    {
        $song = new Song(['name' => 'Única']);

        $this->repo->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($song);

        $result = $this->service->get(1);

        $this->assertEquals('Única', $result->name);
    }

    public function test_update_song()
    {
        $data = ['name' => 'Atualizada'];

        $this->repo->shouldReceive('existsSpotifyId')
            ->zeroOrMoreTimes()
            ->andReturnFalse();

        $updated = new Song($data);

        $this->repo->shouldReceive('update')
            ->once()
            ->with(1, $data)
            ->andReturn($updated);

        $result = $this->service->update(1, $data);

        $this->assertEquals('Atualizada', $result->name);
    }

    public function test_delete_song()
    {
        $this->repo->shouldReceive('delete')
            ->once()
            ->with(1)
            ->andReturn(true);

        $result = $this->service->delete(1);

        $this->assertTrue($result);
    }
}
