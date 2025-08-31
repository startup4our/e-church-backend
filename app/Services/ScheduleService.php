<?php

namespace App\Services;

use App\Models\Schedule;
use App\Repositories\ScheduleRepository;
use App\Services\Interfaces\IScheduleService;
use Illuminate\Database\Eloquent\Collection;

class ScheduleService implements IScheduleService
{
    private ScheduleRepository $repository;

    public function __construct(ScheduleRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(array $data): Schedule
    {
        return $this->repository->create($data);
    }

    public function getAll(): Collection
    {
        return $this->repository->getAll();
    }

    public function getById(int $id): Schedule
    {
        return $this->repository->getById($id);
    }

    public function update(int $id, array $data): Schedule
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
