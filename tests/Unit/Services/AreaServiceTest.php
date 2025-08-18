<?php

namespace Tests\Unit\Services;

use App\Repositories\AreaRepository;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;
use App\Models\Area;
use App\Services\AreaService;
use Mockery;

class AreaServiceTest extends TestCase
{
    private $repositoryMock;
    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(AreaRepository::class);
        $this->service = new AreaService($this->repositoryMock);
    }

    public function test_create_area()
    {
        $data = ['name' => 'Test', 'description' => 'Desc'];
        $expected = new Area($data);

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
        $areas = new Collection([
            new Area(['name' => 'A']),
            new Area(['name' => 'B'])
        ]);

        $this->repositoryMock
            ->shouldReceive('getAll')
            ->once()
            ->andReturn($areas);

        $result = $this->service->getAll();

        $this->assertCount(2, $result);
    }

    public function test_get_by_id()
    {
        $area = new Area(['name' => 'Unique']);

        $this->repositoryMock
            ->shouldReceive('getById')
            ->once()
            ->with(1)
            ->andReturn($area);

        $result = $this->service->getById(1);

        $this->assertEquals('Unique', $result->name);
    }

    public function test_update_area()
    {
        $data = ['name' => 'Updated'];
        $updated = new Area($data);

        $this->repositoryMock
            ->shouldReceive('update')
            ->once()
            ->with(1, $data)
            ->andReturn($updated);

        $result = $this->service->update(1, $data);

        $this->assertEquals('Updated', $result->name);
    }

    public function test_delete_area()
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
