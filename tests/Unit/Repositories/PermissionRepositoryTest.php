<?php

namespace Tests\Unit;

use App\Models\Permission;
use App\Models\User;
use App\Repositories\PermissionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected PermissionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new PermissionRepository();
    }

    public function test_create_and_find_by_id()
    {
        $user = User::factory()->create();

        $data = [
            'user_id' => $user->id,
            'create_scale' => true,
            'read_scale' => true,
            'manage_users' => true,
        ];

        $created = $this->repository->create($data);

        $this->assertDatabaseHas('permission', $data);

        $found = $this->repository->findById($created->id);

        $this->assertNotNull($found);
        $this->assertEquals($created->id, $found->id);
        $this->assertEquals($user->id, $found->user_id);
    }

    public function test_update_changes_fields()
    {
        $permission = Permission::factory()->create(['create_scale' => false]);

        $updated = $this->repository->update($permission, ['create_scale' => true]);

        $this->assertTrue($updated->create_scale);
        $this->assertDatabaseHas('permission', ['id' => $permission->id, 'create_scale' => true]);
    }

    public function test_delete_removes_record()
    {
        $permission = Permission::factory()->create();

        $this->repository->delete($permission);

        $this->assertDatabaseMissing('permission', ['id' => $permission->id]);
    }

    public function test_all_returns_collection()
    {
        Permission::factory()->count(3)->create();

        $all = $this->repository->all();

        $this->assertCount(3, $all);
        $this->assertInstanceOf(Permission::class, $all->first());
    }
}
