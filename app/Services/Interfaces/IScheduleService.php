<?php

namespace App\Services\Interfaces;

use App\Models\Schedule;
use Illuminate\Database\Eloquent\Collection;

interface IScheduleService
{
    public function create(array $data): Schedule;

    public function getAll(): Collection;

    public function getById(int $id): Schedule;

    public function update(int $id, array $data): Schedule;

    public function delete(int $id): bool;
}
