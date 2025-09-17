<?php

namespace App\Services\Interfaces;

use App\Models\PermissionTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface IPermissionTemplateService
{
    public function create(array $data): PermissionTemplate;
    public function list(?string $q = null, int $perPage = 15): LengthAwarePaginator;
    public function get(int $id): PermissionTemplate;
    public function update(int $id, array $data): PermissionTemplate;
    public function delete(int $id): bool;
}
