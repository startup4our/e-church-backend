<?php

namespace App\Services;

use App\Models\DateException;
use App\Repositories\DateExceptionRepository;
use App\Services\Interfaces\IDateExceptionService;

class DateExceptionService implements IDateExceptionService
{
    protected DateExceptionRepository $repository;

    public function __construct(DateExceptionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listAll()
    {
        return $this->repository->all();
    }

    public function create(array $data): DateException
    {
        return $this->repository->create($data);
    }

    public function get(int $id): ?DateException
    {
        return $this->repository->findById($id);
    }

    public function update(DateException $exception, array $data): DateException
    {
        return $this->repository->update($exception, $data);
    }

    public function delete(DateException $exception): void
    {
        $this->repository->delete($exception);
    }
}
