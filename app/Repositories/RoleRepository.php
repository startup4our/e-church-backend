<?php

namespace App\Repositories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class RoleRepository
{
    public function create(array $data): Role
    {
        return Role::create($data);
    }

    public function getAll(): Collection
    {
        return Role::with('area')->orderBy('name')->get();
    }

    public function getById(int $id): Role
    {
        return Role::with('area')->findOrFail($id);
    }

    public function update(int $id, array $data): Role
    {
        $role = Role::findOrFail($id);
        $role->update($data);
        return $role->load('area');
    }

    public function delete(int $id): bool
    {
        $role = Role::findOrFail($id);
        return (bool) $role->delete();
    }

    public function getByAreaId(int $areaId): Collection
    {
        return Role::where('area_id', $areaId)->orderBy('name')->get();
    }

    public function createBatch(array $rolesData, int $areaId): Collection
    {
        $roles = collect();
        foreach ($rolesData as $roleData) {
            $roleData['area_id'] = $areaId;
            $role = $this->create($roleData);
            $roles->push($role);
        }
        return $roles;
    }

    public function deleteByAreaId(int $areaId): void
    {
        Role::where('area_id', $areaId)->delete();
    }
}
