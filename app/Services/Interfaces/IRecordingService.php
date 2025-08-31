<?php

namespace App\Services\Interfaces;

use App\Models\Recording;
use Illuminate\Database\Eloquent\Collection;

interface IRecordingService
{
    public function create(array $data): Recording;

    public function getAll(): Collection;

    public function getById(int $id): Recording;

    public function update(int $id, array $data): Recording;

    public function delete(int $id): bool;
}
