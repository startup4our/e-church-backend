<?php

namespace App\Repositories;

use App\Models\Unavailability;

class UnavailabilityRepository
{
    public function all()
    {
        return Unavailability::with('user')->get();
    }

    public function findById(int $id): ?Unavailability
    {
        return Unavailability::with('user')->find($id);
    }

    public function exists(array $data, ?int $ignoreId = null): bool
    {
        $query = Unavailability::where('user_id', $data['user_id'])
            ->where('weekday', $data['weekday'])
            ->where('shift', $data['shift']);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    public function create(array $data): Unavailability
    {
        return Unavailability::create($data);
    }

    public function update(Unavailability $unavailability, array $data): Unavailability
    {
        $unavailability->update($data);
        return $unavailability;
    }

    public function delete(Unavailability $unavailability): void
    {
        $unavailability->delete();
    }

    public function findByUserId(int $userId)
    {
        return Unavailability::where('user_id', $userId)->get();
    }

    public function syncByUserId(int $userId, array $unavailabilities): void
    {
        // Remove todas as indisponibilidades existentes do usuário
        Unavailability::where('user_id', $userId)->delete();

        // Cria as novas indisponibilidades
        if (!empty($unavailabilities)) {
            $data = array_map(function ($item) use ($userId) {
                return [
                    'user_id' => $userId,
                    'weekday' => $item['weekday'],
                    'shift' => $item['shift'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, $unavailabilities);

            Unavailability::insert($data);
        }
    }
}
