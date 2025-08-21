<?php

namespace App\Services\Interfaces;

use App\Models\Song;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ISongService
{
    public function create(array $data): Song;
    public function list(?string $q = null, int $perPage = 15): LengthAwarePaginator;
    public function get(int $id): Song;
    public function update(int $id, array $data): Song;
    public function delete(int $id): bool;
}
