<?php

namespace App\Services;

use App\Enums\UserStatus;
use App\Models\Invite;
use App\Models\User;
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
        Log::info('Creating invite', $data);

        // Check if user already exists with this email
        $existingUser = User::where('email', $data['email'])->first();
        
        if ($existingUser) {
            $statusMessage = match($existingUser->status) {
                UserStatus::ACTIVE => 'ativo e já pode fazer login',
                UserStatus::WAITING_APPROVAL => 'aguardando aprovação',
                UserStatus::INACTIVE => 'inativo (rejeitado)',
                UserStatus::REJECTED => 'rejeitado',
                default => 'cadastrado no sistema'
            };
            
            Log::warning("Cannot create invite - user already exists", [
                'email' => $data['email'],
                'user_id' => $existingUser->id,
                'status' => $existingUser->status->value
            ]);
            
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                userMessage: "Já existe um usuário cadastrado com este email. Status atual: {$statusMessage}."
            );
        }

        return DB::transaction(function () use ($data) {
            // Extract area_ids and role_ids before creating invite
            $areaIds = $data['area_ids'] ?? [];
            $roleIds = $data['role_ids'] ?? [];
            
            // Remove them from data since they're not in the invite table
            unset($data['area_ids'], $data['role_ids']);
            
            // Generate token and expiration
            $data['token'] = Str::random(40);
            $data['expires_at'] = Carbon::now()->addDays(7);
            $data['used'] = false;

            $invite = $this->repository->create($data);

            // Attach areas and roles via pivot tables
            if (!empty($areaIds)) {
                $invite->areas()->attach($areaIds);
                Log::info("Attached areas to invite", ['invite_id' => $invite->id, 'area_ids' => $areaIds]);
            }
            
            if (!empty($roleIds)) {
                $invite->roles()->attach($roleIds);
                Log::info("Attached roles to invite", ['invite_id' => $invite->id, 'role_ids' => $roleIds]);
            }

            // Load relationships before sending email
            $invite->load(['areas', 'roles', 'church']);

            // Send email
            Mail::to($invite->email)->send(new InviteMail($invite));
            Log::info("Invite email sent", ['invite_id' => $invite->id, 'email' => $invite->email]);

            return $invite;
        });
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
        $invites = $this->repository->getByChurchId($churchId);
        
        // Load relationships for each invite
        $invites->load(['areas', 'roles', 'church']);
        
        return $invites;
    }


    public function delete(int $id): bool
    {
        Log::info("Attempting to delete invite", ['invite_id' => $id]);
        
        // Get invite to check status
        $invite = $this->repository->getById($id);
        
        if (!$invite) {
            throw new AppException(
                ErrorCode::RESOURCE_NOT_FOUND,
                userMessage: 'Convite não encontrado'
            );
        }
        
        // Business rule: Cannot delete accepted/used invites
        if ($invite->used) {
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                userMessage: 'Não é possível deletar um convite que já foi aceito'
            );
        }
        
        Log::info("Deleting invite", ['invite_id' => $id, 'email' => $invite->email]);
        
        return $this->repository->delete($id);
    }

    /**
     * Resend an invite email
     * Extends expiration date and sends new email with same token
     * 
     * @param int $id
     * @param int $churchId
     * @return Invite
     * @throws AppException
     */
    public function resend(int $id, int $churchId): Invite
    {
        Log::info("Resending invite", ['invite_id' => $id, 'church_id' => $churchId]);
        
        // Get invite
        $invite = $this->repository->getById($id);
        
        if (!$invite) {
            throw new AppException(
                ErrorCode::RESOURCE_NOT_FOUND,
                userMessage: 'Convite não encontrado'
            );
        }
        
        // Verify church ownership
        if ($invite->church_id !== $churchId) {
            throw new AppException(
                ErrorCode::PERMISSION_DENIED,
                userMessage: 'Você não tem permissão para reenviar este convite'
            );
        }
        
        // Check if already used
        if ($invite->used) {
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                userMessage: 'Este convite já foi utilizado'
            );
        }
        
        return DB::transaction(function () use ($invite) {
            // Extend expiration date (new 7 days from now)
            $invite->expires_at = Carbon::now()->addDays(7);
            $invite->save();
            
            // Load relationships for email
            $invite->load(['areas', 'roles', 'church']);
            
            // Resend email
            Mail::to($invite->email)->send(new InviteMail($invite));
            
            Log::info("Invite resent successfully", [
                'invite_id' => $invite->id,
                'email' => $invite->email,
                'new_expires_at' => $invite->expires_at
            ]);
            
            return $invite;
        });
    }
}
