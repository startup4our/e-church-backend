<?php

namespace App\Services\Interfaces;

use App\Models\Link;
use Illuminate\Database\Eloquent\Collection;

interface ILinkService
{
    public function create(array $data): Link;

    public function getAll(): Collection;

    public function getById(int $id): Link;

    public function update(int $id, array $data): Link;

    public function delete(int $id): bool;
}
