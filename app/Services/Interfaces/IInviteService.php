<?php

namespace App\Services\Interfaces;

use App\Models\Invite;
use Illuminate\Support\Collection;

interface IInviteService
{
    public function create(array $data): Invite;

    public function getByToken(string $token): Invite;

    public function consume(string $token, array $userData);

    public function deleteExpired(): int;

    public function getAll(): Collection;

    public function getByChurchId(int $churchId): Collection;

    public function delete(int $id): bool;

    public function resend(int $id, int $churchId): Invite;
}
