<?php

namespace App\Services;

use App\Enums\UserStatus;
use App\Exceptions\AppException;
use App\Enums\ErrorCode;
use App\Models\User;
use App\Services\Interfaces\IUserApprovalService;
use Illuminate\Support\Facades\Log;

class UserApprovalService implements IUserApprovalService
{
    public function listPending(int $churchId)
    {
        return User::where('church_id', $churchId)
                ->where('status', UserStatus::WAITING_APPROVAL)
                ->get();
    }

    public function approve(int $id, int $churchId): User
    {
        $user = User::where('id', $id)
                    ->where('church_id', $churchId)
                    ->firstOrFail();

        if ($user->status !== UserStatus::WAITING_APPROVAL) {
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                userMessage: 'Usuário não está em status de aprovação.'
            );
        }

        $user->status = UserStatus::ACTIVE;
        $user->save();

        Log::info("Usuário aprovado", ['user_id' => $user->id]);

        return $user;
    }

    public function reject(int $id, int $churchId): User
    {
        $user = User::where('id', $id)
                    ->where('church_id', $churchId)
                    ->firstOrFail();

        if ($user->status !== UserStatus::WAITING_APPROVAL) {
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                userMessage: 'Usuário não está em status de aprovação.'
            );
        }

        $user->status = UserStatus::INACTIVE;
        $user->save();

        Log::info("Usuário rejeitado", ['user_id' => $user->id]);

        return $user;
    }
}
