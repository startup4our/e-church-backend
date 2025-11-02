<?php

namespace App\Services\Interfaces;

use App\Models\DateException;

interface IHandoutService
{
    public function create(array $data);
    public function update(array $data);
    public function delete(array $data);
    public function getActiveForChurch(int $churchId, int $userId);
    public function getAllForChurch(int $churchId);
    public function getById(int $id);

}
