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
                ->whereIn('status', [UserStatus::WAITING_APPROVAL, UserStatus::REJECTED])
                ->with(['areas.area', 'roles'])
               ->orderByRaw("
                    CASE 
                        WHEN status = ? THEN 2
                        WHEN status = ? THEN 3
                    END
                ", [UserStatus::WAITING_APPROVAL, UserStatus::INACTIVE])
                ->orderBy('created_at', 'desc')
                ->get();
    }

    public function approve(int $id, int $churchId): User
    {
        $user = User::where('id', $id)
                    ->where('church_id', $churchId)
                    ->firstOrFail();

        // Allow approving WAITING_APPROVAL or INACTIVE (rejected) users
        if (!in_array($user->status, [UserStatus::WAITING_APPROVAL, UserStatus::INACTIVE])) {
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                userMessage: 'Apenas usuários aguardando aprovação ou rejeitados podem ser aprovados.'
            );
        }

        $user->status = UserStatus::ACTIVE;
        $user->save();

        Log::info("Usuário aprovado", ['user_id' => $user->id, 'previous_status' => $user->status]);

        return $user;
    }

    public function reject(int $id, int $churchId): User
    {
        $user = User::where('id', $id)
                    ->where('church_id', $churchId)
                    ->firstOrFail();

        // Only allow rejecting users who are waiting for approval
        if ($user->status !== UserStatus::WAITING_APPROVAL) {
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                userMessage: 'Apenas usuários aguardando aprovação podem ser rejeitados.'
            );
        }

        $user->status = UserStatus::REJECTED;
        $user->save();

        Log::info("Usuário rejeitado", ['user_id' => $user->id]);

        return $user;
    }
}
