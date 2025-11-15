<?php

namespace App\Services\Interfaces;

use App\Models\DTO\ScheduleDTO;
use App\Models\UserSchedule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

interface IUserScheduleService
{
    public function create(array $data): UserSchedule;

    public function getAll(): Collection;

    public function getAvailableUsers(): SupportCollection;

    public function getAllSchedules(): SupportCollection;

    public function getMySchedules(): SupportCollection;

    public function getById(int $id): UserSchedule;

    public function getScheduleByScheduleId(int $id): ScheduleDTO;

    public function getUsersByScheduleId(int $id): SupportCollection;

    public function update(array $data): UserSchedule;

    public function deleteUserFromSchedule(array $data): bool;

    public function delete(int $id): bool;
}
