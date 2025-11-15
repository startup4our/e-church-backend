<?php

namespace App\Services\Interfaces;

use App\Models\Unavailability;

interface IUnavailabilityService
{
    public function listAll();
    public function create(array $data): Unavailability;
    public function get(int $id): ?Unavailability;
    public function update(Unavailability $unavailability, array $data): Unavailability;
    public function delete(Unavailability $unavailability): void;
    public function getByUserId(int $userId);
    public function syncByUserId(int $userId, array $unavailabilities): void;
}
