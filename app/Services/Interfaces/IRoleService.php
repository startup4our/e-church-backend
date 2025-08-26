<?php

namespace App\Services\Interfaces;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;

interface IRoleService
{
    public function create(array $data): Role;

    public function getAll(): Collection;

    public function getById(int $id): Role;

    public function update(int $id, array $data): Role;
    
    public function delete(int $id): bool;
}
