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
}
