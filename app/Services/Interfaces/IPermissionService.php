<?php

namespace App\Services\Interfaces;

use App\Models\Permission;

interface IPermissionService
{
    public function listAll();
    public function create(array $data): Permission;
    public function get(int $id): ?Permission;
    public function update(Permission $permission, array $data): Permission;
    public function delete(Permission $permission): void;
}
