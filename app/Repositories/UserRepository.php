<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\UserArea;

class UserRepository
{
    public function create(array $data): User
    {
        return User::create($data);
    }

    public function getById(int $id): ?User
    {
        return User::find($id);
    }

    public function getByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function update(int $id, array $data): User
    {
        $user = User::findOrFail($id);
        $user->update($data);
        return $user;
    }

    public function delete(int $id): bool
    {
        $user = User::find($id);
        return $user ? $user->delete() : false;
    }

    public function attachAreas(int $userId, array $areaIds): void
    {
        foreach ($areaIds as $areaId) {
            UserArea::create([
                'user_id' => $userId,
                'area_id' => $areaId
            ]);
        }
    }

    public function attachRoles(int $userId, array $roleIds): void
    {
        $user = User::findOrFail($userId);
        $user->roles()->attach($roleIds);
    }
}

