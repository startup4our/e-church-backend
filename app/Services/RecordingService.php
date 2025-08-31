<?php

namespace App\Services;

use App\Models\Recording;
use App\Repositories\RecordingRepository;
use App\Services\Interfaces\IRecordingService;
use Illuminate\Database\Eloquent\Collection;

class RecordingService implements IRecordingService
{
    private RecordingRepository $repository;

    public function __construct(RecordingRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(array $data): Recording
    {
        return $this->repository->create($data);
    }

    public function getAll(): Collection
    {
        return $this->repository->getAll();
    }

    public function getById(int $id): Recording
    {
        return $this->repository->getById($id);
    }

    public function update(int $id, array $data): Recording
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
