<?php

namespace App\Repositories;

use App\Models\Schedule;
use Illuminate\Database\Eloquent\Collection;

class ScheduleRepository
{
    protected $model;

    public function __construct(Schedule $schedule)
    {
        $this->model = $schedule;
    }

    public function create(array $data): Schedule
    {
        return $this->model->create($data);
    }

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function getById(int $id): Schedule
    {
        return $this->model->findOrFail($id);
    }

    public function update(int $id, array $data): Schedule
    {
        $schedule = $this->model->findOrFail($id);
        $schedule->update($data);
        return $schedule;
    }

    public function delete(int $id): bool
    {
        $schedule = $this->model->findOrFail($id);
        return $schedule->delete();
    }
}
