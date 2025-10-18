<?php

namespace App\Repositories;

use App\Models\Invite;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;

class InviteRepository
{
    public function create(array $data): Invite
    {
        return Invite::create($data);
    }

    public function getByToken(string $token): ?Invite
    {
        // return Invite::where('token', $token)->first();
        return Invite::where('token', $token)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

    }

    public function markAsUsed(int $id): bool
    {
        return Invite::where('id', $id)->update(['used' => true]);
    }

    public function deleteExpired(): int
    {
        return Invite::where('expires_at', '<', Carbon::now())->delete();
    }

    public function getAll(): Collection
    {
        return Invite::all();
    }

    public function getById(int $id): ?Invite
    {
        return Invite::find($id);
    }

    public function getByChurchId(int $churchId): Collection
    {
        return Invite::where('church_id', $churchId)->get();
    }

    public function delete(int $id): bool
    {
        $invite = Invite::find($id);
        return $invite ? $invite->delete() : false;
    }
}
