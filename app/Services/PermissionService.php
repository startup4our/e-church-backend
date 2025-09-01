<?php

namespace App\Services;

use App\Models\Permission;
use App\Repositories\PermissionRepository;
use App\Services\Interfaces\IPermissionService;

class PermissionService implements IPermissionService
{
    protected PermissionRepository $repository;

    public function __construct(PermissionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listAll()
    {
        return $this->repository->all();
    }

    public function create(array $data): Permission
    {
        return $this->repository->create($data);
    }

    public function get(int $id): ?Permission
    {
        return $this->repository->findById($id);
    }

    public function update(Permission $permission, array $data): Permission
    {
        return $this->repository->update($permission, $data);
    }

    public function delete(Permission $permission): void
    {
        $this->repository->delete($permission);
    }

    public function canCreateScale(int $userId): bool
    {
        return $this->hasPermission($userId, 'create_scale');
    }

    public function hasPermission(int $userId, string $permissionField): bool
    {
        $permissions = $this->repository->getPermissionsByUser($userId);
        return isset($permissions[$permissionField]) && $permissions[$permissionField];
    }

    public function getUserPermissions(int $userId): array
    {
        return $this->repository->getPermissionsByUser($userId);
    }
}
