<?php

namespace Tests\Unit;

use App\Models\Permission;
use App\Repositories\PermissionRepository;
use App\Services\PermissionService;
use Tests\TestCase;
use Mockery;

class PermissionServiceTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_permission()
    {
        $repo = Mockery::mock(PermissionRepository::class);
        $repo->shouldReceive('create')->once()->andReturn(new Permission(['id' => 1]));

        $service = new PermissionService($repo);

        $data = [
            'user_id' => 1,
            'create_scale' => true,
            'read_scale' => true,
            'manage_users' => true,
        ];

        $permission = $service->create($data);

        $this->assertInstanceOf(Permission::class, $permission);
    }
}
