<?php

namespace App\Repositories;

use App\Models\UserArea;
use Illuminate\Database\Eloquent\Collection;

class UserAreaRepository
{
    protected $model;

    public function __construct(UserArea $userArea)
    {
        $this->model = $userArea;
    }

    public function getAreasByUserId(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)->with('area')->get();
    }

    public function getUsersByAreaId(int $areaId): Collection
    {
        return $this->model->where('area_id', $areaId)->with('user')->get();
    }

    public function userBelongsToArea(int $userId, int $areaId): bool
    {
        return $this->model->where('user_id', $userId)
                          ->where('area_id', $areaId)
                          ->exists();
    }

    public function addUserToArea(int $userId, int $areaId): UserArea
    {
        return $this->model->create([
            'user_id' => $userId,
            'area_id' => $areaId
        ]);
    }

    public function removeUserFromArea(int $userId, int $areaId): bool
    {
        return $this->model->where('user_id', $userId)
                          ->where('area_id', $areaId)
                          ->delete();
    }
}