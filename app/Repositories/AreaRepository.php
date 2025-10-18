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

    public function getByChurchId(int $churchId): Collection
    {
        return Area::where('church_id', $churchId)->get();
    }

    public function getByChurchIdWithRoles(int $churchId): Collection
    {
        return Area::where('church_id', $churchId)
            ->with('roles')
            ->get();
    }

    public function getById(int $id): Area
    {
        return Area::findOrFail($id);
    }

    public function getByIdAndChurchId(int $id, int $churchId): Area
    {
        return Area::where('id', $id)->where('church_id', $churchId)->firstOrFail();
    }

    public function update(int $id, array $data): Area
    {
        $area = Area::findOrFail($id);
        $area->update($data);
        return $area;
    }

    public function updateByIdAndChurchId(int $id, int $churchId, array $data): Area
    {
        $area = Area::where('id', $id)->where('church_id', $churchId)->firstOrFail();
        $area->update($data);
        return $area;
    }

    public function delete(int $id): bool
    {
        $area = Area::findOrFail($id);
        return $area->delete();
    }

    public function deleteByIdAndChurchId(int $id, int $churchId): bool
    {
        $area = Area::where('id', $id)->where('church_id', $churchId)->firstOrFail();
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
