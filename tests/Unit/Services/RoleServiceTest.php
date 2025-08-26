<?php

namespace Tests\Unit\Services;

use App\Models\Role;
use App\Repositories\RoleRepository;
use App\Services\RoleService;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Tests\TestCase;

class RoleServiceTest extends TestCase
{
    private $repo;
    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = Mockery::mock(RoleRepository::class);
        $this->service = new RoleService($this->repo);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_role()
    {
        $data = ['name' => 'Novo', 'description' => 'Desc', 'area_id' => 1];
        $expected = new Role($data);

        $this->repo->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($expected);

        $result = $this->service->create($data);

        $this->assertEquals($expected, $result);
    }

    public function test_get_all()
    {
        $items = new Collection([new Role(['name' => 'A']), new Role(['name' => 'B'])]);

        $this->repo->shouldReceive('getAll')
            ->once()
            ->andReturn($items);

        $result = $this->service->getAll();

        $this->assertCount(2, $result);
    }

    public function test_get_by_id()
    {
        $item = new Role(['name' => 'Única']);

        $this->repo->shouldReceive('getById')
            ->once()
            ->with(1)
            ->andReturn($item);

        $result = $this->service->getById(1);

        $this->assertEquals('Única', $result->name);
    }

    public function test_update_role()
    {
        $data = ['name' => 'Atualizada'];
        $updated = new Role($data);

        $this->repo->shouldReceive('update')
            ->once()
            ->with(1, $data)
            ->andReturn($updated);

        $result = $this->service->update(1, $data);

        $this->assertEquals('Atualizada', $result->name);
    }

    public function test_delete_role()
    {
        $this->repo->shouldReceive('delete')
            ->once()
            ->with(1)
            ->andReturn(true);

        $result = $this->service->delete(1);

        $this->assertTrue($result);
    }
}

