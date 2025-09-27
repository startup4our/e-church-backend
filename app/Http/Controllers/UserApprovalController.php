<?php

namespace App\Http\Controllers;

use App\Exceptions\AppException;
use App\Enums\ErrorCode;
use App\Services\Interfaces\IUserApprovalService;
use App\Services\Interfaces\IPermissionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserApprovalController extends Controller
{
    private IUserApprovalService $approvalService;
    private IPermissionService $permissionService;

    public function __construct(IUserApprovalService $approvalService, IPermissionService $permissionService)
    {
        $this->approvalService = $approvalService;
        $this->permissionService = $permissionService;
    }

    public function index()
    {
        $user = Auth::user();
        Log::info("User [{$user->id}] requested to list pending users in church [{$user->church_id}]");

        // Check permission
        if (!$this->permissionService->hasPermission($user->id, 'manage_users')) {
            Log::warning("User [{$user->id}] attempted to list pending users without permission");
            throw new AppException(
                ErrorCode::PERMISSION_DENIED,
                userMessage: 'Você não tem permissão para aprovar usuários'
            );
        }

        try {
            $pending = $this->approvalService->listPending($user->church_id);
            Log::info("Retrieved " . $pending->count() . " pending users for church [{$user->church_id}]");

            return response()->json([
                'success' => true,
                'data' => $pending
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to list pending users for church [{$user->church_id}]: " . $e->getMessage());
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro ao buscar usuários pendentes'
            );
        }
    }

    public function approve(int $id)
    {
        $user = Auth::user();
        Log::info("User [{$user->id}] attempting to approve user [{$id}] in church [{$user->church_id}]");

        if (!$this->permissionService->hasPermission($user->id, 'manage_users')) {
            Log::warning("User [{$user->id}] attempted to approve user [{$id}] without permission");
            throw new AppException(
                ErrorCode::PERMISSION_DENIED,
                userMessage: 'Você não tem permissão para aprovar usuários'
            );
        }

        try {
            $approved = $this->approvalService->approve($id, $user->church_id);
            Log::info("User [{$id}] approved successfully by [{$user->id}]");

            return response()->json([
                'success' => true,
                'data' => $approved
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to approve user [{$id}] by [{$user->id}]: " . $e->getMessage());
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro ao aprovar usuário'
            );
        }
    }

    public function reject(int $id)
    {
        $user = Auth::user();
        Log::info("User [{$user->id}] attempting to reject user [{$id}] in church [{$user->church_id}]");

        if (!$this->permissionService->hasPermission($user->id, 'manage_users')) {
            Log::warning("User [{$user->id}] attempted to reject user [{$id}] without permission");
            throw new AppException(
                ErrorCode::PERMISSION_DENIED,
                userMessage: 'Você não tem permissão para rejeitar usuários'
            );
        }

        try {
            $rejected = $this->approvalService->reject($id, $user->church_id);
            Log::info("User [{$id}] rejected successfully by [{$user->id}]");

            return response()->json([
                'success' => true,
                'data' => $rejected
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to reject user [{$id}] by [{$user->id}]: " . $e->getMessage());
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro ao rejeitar usuário'
            );
        }
    }
}
