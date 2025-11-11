<?php

namespace App\Services;

use App\Models\Role;
use App\Repositories\RoleRepository;
use App\Services\Interfaces\IRoleService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

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

    public function getByAreaId(int $areaId): Collection
    {
        Log::info("Retrieving roles for area [{$areaId}]");
        
        try {
            $roles = $this->repository->getByAreaId($areaId);
            Log::info("Retrieved " . $roles->count() . " roles for area [{$areaId}]");
            return $roles;
        } catch (\Exception $e) {
            Log::error("Failed to retrieve roles for area [{$areaId}]: " . $e->getMessage());
            throw $e;
        }
    }

    public function createBatch(array $rolesData, int $areaId): Collection
    {
        Log::info("Creating " . count($rolesData) . " roles for area [{$areaId}]");
        
        try {
            $roles = $this->repository->createBatch($rolesData, $areaId);
            Log::info("Successfully created " . $roles->count() . " roles for area [{$areaId}]");
            return $roles;
        } catch (\Exception $e) {
            Log::error("Failed to create roles for area [{$areaId}]: " . $e->getMessage());
            throw $e;
        }
    }
}
