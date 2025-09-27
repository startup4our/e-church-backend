<?php

namespace App\Services\Interfaces;

use App\Models\User;

interface IUserApprovalService
{
    /**
     * Lista usuários aguardando aprovação
     * @return \Illuminate\Database\Eloquent\Collection|User[]
     */
    public function listPending(int $churchId);

    /**
     * Aprova um usuário
     * @param int $id
     * @return User
     */
    public function approve(int $id, int $churchId): User;

    /**
     * Rejeita um usuário
     * @param int $id
     * @return User
     */
    public function reject(int $id, int $churchId): User;
}
