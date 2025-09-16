<?php

namespace App\Services;

use App\Models\DTO\ScheduleDTO;
use App\Models\DTO\UserScheduleDetailsDTO;
use App\Models\UserSchedule;
use App\Repositories\UserScheduleRepository;
use App\Services\Interfaces\IUserScheduleService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class UserScheduleService implements IUserScheduleService
{
    private UserScheduleRepository $repository;

    public function __construct(UserScheduleRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(array $data): UserSchedule
    {
        return $this->repository->create($data);
    }

    public function getAll(): Collection
    {
        return $this->repository->getAll();
    }

    public function getAllSchedules(): SupportCollection
    {
        $schedules = $this->repository->getAllSchedules();
        $schedules = $schedules->map(fn($schedule) => new ScheduleDTO($schedule));

        return $schedules;
    }

    public function getById(int $id): UserSchedule
    {
        return $this->repository->getById($id);
    }

    public function getUsersByScheduleId(int $id): SupportCollection
    {
        $users = $this->repository->getUsersByScheduleId($id);
        $users = $users->map(fn($user) => new UserScheduleDetailsDTO($user));

        return $users;
    }

    public function update(array $data): UserSchedule
    {
        return $this->repository->update($data);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
