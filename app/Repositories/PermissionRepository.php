<?php

namespace App\Repositories;

use App\Models\Permission;

class PermissionRepository
{
    public function all()
    {
        return Permission::with('user')->get();
    }

    public function findById(int $id): ?Permission
    {
        return Permission::with('user')->find($id);
    }

    public function create(array $data): Permission
    {
        return Permission::create($data);
    }

    public function update(Permission $permission, array $data): Permission
    {
        $permission->update($data);
        return $permission;
    }

    public function delete(Permission $permission): void
    {
        $permission->delete();
    }
}
