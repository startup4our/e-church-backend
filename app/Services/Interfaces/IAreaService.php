<?php

namespace App\Services\Interfaces;

use App\Models\Area;
use Illuminate\Support\Collection;

interface IAreaService
{
    public function create(array $data): Area;

    public function getAll(): Collection;

    public function getByChurchId(int $churchId): Collection;

    public function getById(int $id): Area;

    public function getByIdAndChurchId(int $id, int $churchId): Area;

    public function update(int $id, array $data): Area;

    public function updateByIdAndChurchId(int $id, int $churchId, array $data): Area;

    public function delete(int $id): bool;

    public function deleteByIdAndChurchId(int $id, int $churchId): bool;

    public function getUserAreas(int $user_id): Collection|Area;
}
