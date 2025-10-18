<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\IInviteService;
use App\Services\Interfaces\IPermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Exceptions\AppException;
use App\Enums\ErrorCode;

class InviteController extends Controller
{
    private IInviteService $inviteService;
    private IPermissionService $permissionService;

    public function __construct(IInviteService $inviteService, IPermissionService $permissionService)
    {
        $this->inviteService = $inviteService;
        $this->permissionService = $permissionService;
    }

    public function index()
    {
        $user = Auth::user();

        Log::info("User [{$user->id}] listing invites for church [{$user->church_id}]");

        $invites = $this->inviteService->getByChurchId($user->church_id);

        return response()->json([
            'success' => true,
            'data' => $invites
        ]);
    }


    public function show(string $token)
    {
        try {
            $invite = $this->inviteService->getByToken($token);
            
            // Load relationships
            $invite->load(['areas', 'roles', 'church']);
            
            return response()->json(['success' => true, 'data' => $invite]);
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::INVITE_EXPIRED,
                userMessage: "Convite expirado ou inválido"
            );
        }
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$this->permissionService->hasPermission($user->id, 'manage_users')) {
            throw new AppException(
                    ErrorCode::PERMISSION_DENIED,
                    userMessage: 'Você não tem permissão para enviar convites.'
                );
        }
                
        $data = $request->validate([
            'email' => 'required|email',
            'area_ids' => 'required|array',
            'area_ids.*' => 'exists:area,id',
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:role,id'
        ]);

        // Preenche automaticamente church_id do usuário logado
        $data['church_id'] = $user->church_id;

        Log::info("Creating invite", ['user_id' => $user->id, 'data' => $data]);

        $invite = $this->inviteService->create($data);
        
        return response()->json([
            'success' => true, 
            'message' => 'Convite enviado com sucesso',
            'data' => $invite
        ], 201);
    }

    // Consome convite (registro)
    public function consume(Request $request)
    {
        $data = $request->validate([
            'token' => 'required',
        ]);

        $userData = $request->except('token');
        $user = $this->inviteService->consume($data['token'], $userData);
        return response()->json(['success' => true, 'data' => $user]);
    }

    public function destroy(int $id)
    {
        $user = Auth::user();

        if (!$this->permissionService->hasPermission($user->id, 'manage_users')) {
            throw new AppException(
                ErrorCode::PERMISSION_DENIED,
                userMessage: 'Você não tem permissão para apagar convites.'
            );
        }

        $this->inviteService->delete($id);
        return response()->json(['success' => true, 'data' => null], 204);
    }

    /**
     * Resend an invite email
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function resend(int $id)
    {
        $user = Auth::user();
        
        // Check permissions
        if (!$this->permissionService->hasPermission($user->id, 'manage_users')) {
            throw new AppException(
                ErrorCode::PERMISSION_DENIED,
                userMessage: 'Você não tem permissão para reenviar convites.'
            );
        }
        
        try {
            Log::info("User [{$user->id}] resending invite [{$id}]");
            
            $invite = $this->inviteService->resend($id, $user->church_id);
            
            return response()->json([
                'success' => true,
                'message' => 'Convite reenviado com sucesso',
                'data' => $invite
            ]);
        } catch (AppException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Failed to resend invite", [
                'invite_id' => $id, 
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro ao reenviar convite'
            );
        }
    }
}
