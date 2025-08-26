<?php

namespace App\Services;

use App\Models\Role;
use App\Repositories\RoleRepository;
use App\Services\Interfaces\IRoleService;
use Illuminate\Database\Eloquent\Collection;

class RoleService implements IRoleService
{
    public function __construct(private RoleRepository $repository) {}

    public function create(array $data): Role
    {
        return $this->repository->create($data);
    }

    public function getAll(): Collection
    {
        return $this->repository->getAll();
    }

    public function getById(int $id): Role
    {
        return $this->repository->getById($id);
    }

    public function update(int $id, array $data): Role
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
