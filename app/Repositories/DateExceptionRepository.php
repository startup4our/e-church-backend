<?php

namespace App\Repositories;

use App\Models\DateException;

class DateExceptionRepository
{
    public function all()
    {
        return DateException::with('user')->get();
    }

    public function findById(int $id): ?DateException
    {
        return DateException::with('user')->find($id);
    }

    public function create(array $data): DateException
    {
        return DateException::create($data);
    }

    public function update(DateException $exception, array $data): DateException
    {
        $exception->update($data);
        return $exception;
    }

    public function delete(DateException $exception): void
    {
        $exception->delete();
    }
}
