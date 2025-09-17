<?php

namespace Tests\Unit;

use App\Models\PermissionTemplate;
use App\Repositories\PermissionTemplateRepository;
use App\Services\PermissionTemplateService;
use Mockery;
use Tests\TestCase;

class PermissionTemplateServiceTest extends TestCase
{
    private $repo;
    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = Mockery::mock(PermissionTemplateRepository::class);
        $this->service = new PermissionTemplateService($this->repo);
    }

    public function test_create_template()
    {
        $data = ['name' => 'Leader default'];
        $expected = new PermissionTemplate($data);

        $this->repo->shouldReceive('create')->once()->with($data)->andReturn($expected);

        $result = $this->service->create($data);

        $this->assertEquals('Leader default', $result->name);
    }
}
