<?php

namespace Tests\Unit\Services;

use App\Models\Church;
use App\Repositories\ChurchRepository;
use App\Services\ChurchService;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Tests\TestCase;

class ChurchServiceTest extends TestCase
{
    private $repositoryMock;
    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(ChurchRepository::class);
        $this->service = new ChurchService($this->repositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_church()
    {
        $data = ['name' => 'Nova Igreja', 'city' => 'Itajubá'];

        $this->repositoryMock
            ->shouldReceive('existsDuplicate')
            ->once()
            ->with($data)
            ->andReturn(false);


        $expected = new Church($data);

        $this->repositoryMock
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($expected);

        $result = $this->service->create($data);

        $this->assertEquals($expected, $result);
    }

    public function test_get_all()
    {
        $items = new Collection([
            new Church(['name' => 'A']),
            new Church(['name' => 'B']),
        ]);

        $this->repositoryMock
            ->shouldReceive('getAll')
            ->once()
            ->andReturn($items);

        $result = $this->service->getAll();

        $this->assertCount(2, $result);
    }

    public function test_get_by_id()
    {
        $church = new Church(['name' => 'Única']);

        $this->repositoryMock
            ->shouldReceive('getById')
            ->once()
            ->with(1)
            ->andReturn($church);

        $result = $this->service->getById(1);

        $this->assertEquals('Única', $result->name);
    }

    public function test_update_church()
    {
        $data = ['name' => 'Atualizada'];

        $this->repositoryMock
            ->shouldReceive('existsDuplicate')
            ->once()
            ->with($data, 1)
            ->andReturn(false);


        $updated = new Church($data);

        $this->repositoryMock
            ->shouldReceive('update')
            ->once()
            ->with(1, $data)
            ->andReturn($updated);

        $result = $this->service->update(1, $data);

        $this->assertEquals('Atualizada', $result->name);
    }

    public function test_delete_church()
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
