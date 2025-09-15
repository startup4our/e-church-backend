<?php

namespace App\Repositories;

use App\Models\Area;
use App\Models\UserArea;
use Illuminate\Support\Collection;

class AreaRepository 
{
    public function create(array $data): Area
    {
        return Area::create($data);
    }

    public function getAll(): Collection
    {
        return Area::all();
    }

    public function getById(int $id): Area
    {
        return Area::findOrFail($id);
    }

    public function update(int $id, array $data): Area
    {
        $area = Area::findOrFail($id);
        $area->update($data);
        return $area;
    }

    public function delete(int $id): bool
    {
        $area = Area::findOrFail($id);
        return $area->delete();
    }

    public function getUserArea(int $userId): Collection
    {
        return Area::whereIn(
            'id',
            UserArea::where('user_id', $userId)->pluck('area_id')
        )->get();
    }
}
