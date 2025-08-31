<?php

namespace App\Services;

use App\Models\Link;
use App\Repositories\LinkRepository;
use App\Services\Interfaces\ILinkService;
use Illuminate\Database\Eloquent\Collection;

class LinkService implements ILinkService
{
    private LinkRepository $repository;

    public function __construct(LinkRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(array $data): Link
    {
        return $this->repository->create($data);
    }

    public function getAll(): Collection
    {
        return $this->repository->getAll();
    }

    public function getById(int $id): Link
    {
        return $this->repository->getById($id);
    }

    public function update(int $id, array $data): Link
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
