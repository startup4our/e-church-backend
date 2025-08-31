<?php

namespace Tests\Unit\Services;

use App\Repositories\LinkRepository;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;
use App\Models\Link;
use App\Services\LinkService;
use Mockery;

class LinkServiceTest extends TestCase
{
    private $repositoryMock;
    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(LinkRepository::class);
        $this->service = new LinkService($this->repositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_link()
    {
        $data = [
            'name' => 'Test Link',
            'destination' => 'https://example.com',
            'description' => 'A test link',
            'song_id' => 1
        ];
        $expected = new Link($data);

        $this->repositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($expected);

        $result = $this->service->create($data);

        $this->assertEquals($expected, $result);
        $this->assertEquals('Test Link', $result->name);
        $this->assertEquals('https://example.com', $result->destination);
    }

    public function test_get_all()
    {
        $links = new Collection([
            new Link(['name' => 'Link 1', 'destination' => 'https://link1.com']),
            new Link(['name' => 'Link 2', 'destination' => 'https://link2.com'])
        ]);

        $this->repositoryMock
            ->shouldReceive('getAll')
            ->once()
            ->andReturn($links);

        $result = $this->service->getAll();

        $this->assertCount(2, $result);
        $this->assertEquals('Link 1', $result->first()->name);
        $this->assertEquals('Link 2', $result->last()->name);
    }

    public function test_get_by_id()
    {
        $link = new Link([
            'name' => 'Unique Link',
            'destination' => 'https://unique.com'
        ]);

        $this->repositoryMock
            ->shouldReceive('getById')
            ->once()
            ->with(1)
            ->andReturn($link);

        $result = $this->service->getById(1);

        $this->assertEquals('Unique Link', $result->name);
        $this->assertEquals('https://unique.com', $result->destination);
    }

    public function test_update_link()
    {
        $data = [
            'name' => 'Updated Link',
            'destination' => 'https://updated.com'
        ];
        $updated = new Link($data);

        $this->repositoryMock
            ->shouldReceive('update')
            ->once()
            ->with(1, $data)
            ->andReturn($updated);

        $result = $this->service->update(1, $data);

        $this->assertEquals('Updated Link', $result->name);
        $this->assertEquals('https://updated.com', $result->destination);
    }

    public function test_delete_link()
    {
        $this->repositoryMock
            ->shouldReceive('delete')
            ->once()
            ->with(1)
            ->andReturn(true);

        $result = $this->service->delete(1);

        $this->assertTrue($result);
    }

    public function test_create_link_with_description()
    {
        $data = [
            'name' => 'Link with Description',
            'destination' => 'https://example.com',
            'description' => 'This is a detailed description',
            'song_id' => 2
        ];
        $expected = new Link($data);

        $this->repositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($expected);

        $result = $this->service->create($data);

        $this->assertEquals('Link with Description', $result->name);
        $this->assertEquals('This is a detailed description', $result->description);
        $this->assertEquals(2, $result->song_id);
    }

    public function test_get_all_empty_collection()
    {
        $links = new Collection();

        $this->repositoryMock
            ->shouldReceive('getAll')
            ->once()
            ->andReturn($links);

        $result = $this->service->getAll();

        $this->assertCount(0, $result);
        $this->assertEmpty($result);
    }
}
