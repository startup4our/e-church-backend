<?php

namespace App\Services\Interfaces;

use App\Models\Invite;
use App\Models\User;

interface IUserService
{
    public function create(array $data): User;

    public function getById(int $id): ?User;

    public function update(int $id, array $data): User;

    public function delete(int $id): bool;

    public function createByInvite(array $userData, Invite $invite): User;
}

