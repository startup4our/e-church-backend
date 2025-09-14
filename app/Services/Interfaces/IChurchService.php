<?php

namespace App\Services\Interfaces;

use App\Models\Church;
use Illuminate\Database\Eloquent\Collection;

interface IChurchService
{
    public function create(array $data): Church;

    public function getAll(): Collection;

    public function getById(string $id): Church;

    public function update(string $id, array $data): Church;
    
    public function delete(string $id): bool;

    public function getChurchesForRegister(): array;
}
