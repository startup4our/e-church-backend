<?php

namespace App\Services\Interfaces;

use App\Models\UserSchedule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

interface IUserScheduleService
{
    public function create(array $data): UserSchedule;

    public function getAll(): Collection;

    public function getAllSchedules(): SupportCollection;

    public function getById(int $id): UserSchedule;

    public function getUsersByScheduleId(int $id): SupportCollection;

    public function update(array $data): UserSchedule;

    public function delete(int $id): bool;
}
