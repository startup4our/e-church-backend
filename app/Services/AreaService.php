<?php

namespace App\Services;

use App\Models\Area;
use App\Repositories\AreaRepository;
use App\Services\Interfaces\IAreaService;
use Illuminate\Database\Eloquent\Collection;

class AreaService implements IAreaService
{
    private AreaRepository $repository;

    public function __construct(AreaRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(array $data): Area
    {
        return $this->repository->create($data);
    }

    public function getAll(): Collection
    {
        return $this->repository->getAll();
    }

    public function getById(int $id): Area
    {
        return $this->repository->getById($id);
    }

    public function update(int $id, array $data): Area
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
