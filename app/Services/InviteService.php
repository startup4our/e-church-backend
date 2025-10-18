<?php

namespace App\Services;

use App\Models\Invite;
use App\Repositories\InviteRepository;
use App\Services\Interfaces\IInviteService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\InviteMail;
use Illuminate\Support\Carbon;
use App\Exceptions\AppException;
use App\Enums\ErrorCode;

class InviteService implements IInviteService
{
    private InviteRepository $repository;

    public function __construct(InviteRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(array $data): Invite
    {
        Log::info('Entrou no método create do InviteService', $data);
        Log::info('Creating invite: '.json_encode($data));

        // return DB::transaction(function () use ($data) {
            $data['token'] = Str::random(40);
            $data['expires_at'] = Carbon::now()->addDays(7);
            $data['used'] = false;

            $invite = $this->repository->create($data);

            Mail::to($invite->email)->send(new InviteMail($invite));

            // Mail::raw('Corpo do email', function ($message) {
            //     $message->to($invite->email)->subject('Assunto do Email');
            // });

            return $invite;
            // return true;
        // });
    }

    public function getByToken(string $token): Invite
    {
        Log::info('Retrieving invite by token');
        $invite = $this->repository->getByToken($token);

        if (!$invite || $invite->used || $invite->expires_at->isPast()) {
            Log::warning("Invalid or expired invite accessed: " . $token);
            throw new AppException(ErrorCode::INVITE_EXPIRED, userMessage: "Convite expirado ou inválido.");
        }

        return $invite;
    }

    public function consume(string $token, array $userData)
    {
        Log::info('Consuming invite with token: ' . $token);

        return DB::transaction(function () use ($token, $userData) {
            $invite = $this->getByToken($token);

            // Regra: criar usuário e vincular áreas / igreja
            $user = app(\App\Services\UserService::class)->createByInvite($userData, $invite);
            $this->repository->markAsUsed($invite->id);

            Log::info("Invite [{$invite->id}] used by user [{$user->id}]");

            return $user;
        });
    }

    public function deleteExpired(): int
    {
        Log::info('Deleting expired invites');
        return $this->repository->deleteExpired();
    }

    public function getAll(): Collection
    {
        return $this->repository->getAll();
    }

    public function getByChurchId(int $churchId): Collection
    {
        return $this->repository->getByChurchId($churchId);
    }


    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
