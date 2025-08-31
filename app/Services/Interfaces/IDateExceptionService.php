<?php

namespace App\Services\Interfaces;

use App\Models\DateException;

interface IDateExceptionService
{
    public function listAll();
    public function create(array $data): DateException;
    public function get(int $id): ?DateException;
    public function update(DateException $exception, array $data): DateException;
    public function delete(DateException $exception): void;
}
