<?php

namespace App\Services\Interfaces;

use App\Models\Area;
use Illuminate\Database\Eloquent\Collection;

interface IAreaService
{
    public function create(array $data): Area;

    public function getAll(): Collection;

    public function getById(int $id): Area;

    public function update(int $id, array $data): Area;

    public function delete(int $id): bool;
}
