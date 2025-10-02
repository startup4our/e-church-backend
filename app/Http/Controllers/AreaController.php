<?php

namespace App\Http\Controllers;

use App\Exceptions\AppException;
use App\Enums\ErrorCode;
use App\Services\Interfaces\IAreaService;
use App\Services\Interfaces\IPermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AreaController extends Controller
{
    private IAreaService $areaService;
    private IPermissionService $permissionService;

    public function __construct(IAreaService $areaService, IPermissionService $permissionService)
    {
        $this->areaService = $areaService;
        $this->permissionService = $permissionService;
    }

    public function index()
    {
        $user = Auth::user();
        Log::info("User [{$user->id}] requested to list areas for church [{$user->church_id}]");
        
        $areas = $this->areaService->getByChurchId($user->church_id);
        Log::info("Retrieved " . $areas->count() . " areas for user [{$user->id}] from church [{$user->church_id}]");
        
        return response()->json([
            'success' => true,
            'data' => $areas
        ]);
    }

    public function show(int $id)
    {
        $user = Auth::user();
        Log::info("User [{$user->id}] requested to view area [{$id}]");
        
        try {
            $area = $this->areaService->getByIdAndChurchId($id, $user->church_id);
            Log::info("Area [{$id}] retrieved successfully for user [{$user->id}] from church [{$user->church_id}]");
            
            return response()->json([
                'success' => true,
                'data' => $area
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to retrieve area [{$id}] for user [{$user->id}] from church [{$user->church_id}]: " . $e->getMessage());
            throw new AppException(
                ErrorCode::AREA_NOT_FOUND,
                userMessage: 'Área não encontrada'
            );
        }
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Check if user has permission to create areas
        if (!$this->permissionService->hasPermission($user->id, 'create_area')) {
            Log::warning("User [{$user->id}] attempted to create area without permission");
            throw new AppException(
                ErrorCode::PERMISSION_DENIED,
                userMessage: 'Você não tem permissão para criar áreas'
            );
        }

        Log::info("User [{$user->id}] attempting to create new area");

        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
            ]);

            $data['church_id'] = $user->church_id;
            $area = $this->areaService->create($data);
            Log::info("Area [{$area->id}] '{$area->name}' created successfully by user [{$user->id}]");
            
            return response()->json([
                'success' => true,
                'data' => $area
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning("Area creation validation failed for user [{$user->id}]: " . json_encode($e->errors()));
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                details: $e->errors()
            );
        } catch (\Exception $e) {
            Log::error("Failed to create area for user [{$user->id}]: " . $e->getMessage());
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }

    public function update(Request $request, int $id)
    {
        $user = Auth::user();
        
        // Check if user has permission to update areas
        if (!$this->permissionService->hasPermission($user->id, 'update_area')) {
            Log::warning("User [{$user->id}] attempted to update area [{$id}] without permission");
            throw new AppException(
                ErrorCode::PERMISSION_DENIED,
                userMessage: 'Você não tem permissão para editar áreas'
            );
        }

        Log::info("User [{$user->id}] attempted to update area [{$id}] details");

        try {
            $data = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string|max:1000',
            ]);

            $area = $this->areaService->updateByIdAndChurchId($id, $user->church_id, $data);
            Log::info("User [{$user->id}] successfully updated area [{$id}] '{$area->name}' details");
            
            return response()->json([
                'success' => true,
                'data' => $area
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning("User [{$user->id}] failed to update area [{$id}] because of validation errors: " . json_encode($e->errors()));
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                details: $e->errors()
            );
        } catch (\Exception $e) {
            Log::error("User [{$user->id}] failed to update area [{$id}] because: " . $e->getMessage());
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }

    public function destroy(int $id)
    {
        $user = Auth::user();
        
        // Check if user has permission to delete areas
        if (!$this->permissionService->hasPermission($user->id, 'delete_area')) {
            Log::warning("User [{$user->id}] attempted to delete area [{$id}] without permission");
            throw new AppException(
                ErrorCode::PERMISSION_DENIED,
                userMessage: 'Você não tem permissão para excluir áreas'
            );
        }

        Log::info("User [{$user->id}] attempted to delete area [{$id}]");

        try {
            $this->areaService->deleteByIdAndChurchId($id, $user->church_id);
            Log::info("User [{$user->id}] successfully deleted area [{$id}]");
            
            return response()->json([
                'success' => true,
                'data' => null
            ], 204);
        } catch (\Exception $e) {
            Log::error("User [{$user->id}] failed to delete area [{$id}] because: " . $e->getMessage());
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: $e->getMessage()
            );
        }
    }

    public function getUsers(int $id)
    {
        $user = Auth::user();
        
        // Check if user has permission to read areas
        if (!$this->permissionService->hasPermission($user->id, 'read_area')) {
            Log::warning("User [{$user->id}] attempted to view users in area [{$id}] without permission");
            throw new AppException(
                ErrorCode::PERMISSION_DENIED,
                userMessage: 'Você não tem permissão para visualizar usuários das áreas'
            );
        }

        Log::info("User [{$user->id}] requested to view users in area [{$id}]");

        try {
            $areaUsers = $this->areaService->getUsersByAreaId($id, $user->church_id);
            Log::info("User [{$user->id}] successfully retrieved " . $areaUsers->count() . " users from area [{$id}]");
            
            return response()->json([
                'success' => true,
                'data' => $areaUsers
            ]);
        } catch (\Exception $e) {
            Log::error("User [{$user->id}] failed to retrieve users from area [{$id}] because: " . $e->getMessage());
            throw new AppException(
                ErrorCode::AREA_NOT_FOUND,
                userMessage: 'Área não encontrada'
            );
        }
    }

    public function switchUserArea(Request $request, int $areaId, int $userId)
    {
        $user = Auth::user();
        
        // Check if user has permission to update areas
        if (!$this->permissionService->hasPermission($user->id, 'update_area')) {
            Log::warning("User [{$user->id}] attempted to switch user [{$userId}] to area [{$areaId}] without permission");
            throw new AppException(
                ErrorCode::PERMISSION_DENIED,
                userMessage: 'Você não tem permissão para gerenciar usuários das áreas'
            );
        }

        Log::info("User [{$user->id}] attempted to switch user [{$userId}] to area [{$areaId}]");

        try {
            $data = $request->validate([
                'new_area_id' => 'required|integer|exists:area,id',
            ]);

            $this->areaService->switchUserArea($userId, $areaId, $data['new_area_id'], $user->church_id);
            Log::info("User [{$user->id}] successfully switched user [{$userId}] from area [{$areaId}] to area [{$data['new_area_id']}]");
            
            return response()->json([
                'success' => true,
                'message' => 'Usuário movido para nova área com sucesso'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning("User [{$user->id}] failed to switch user [{$userId}] to area [{$areaId}] because of validation errors: " . json_encode($e->errors()));
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                details: $e->errors()
            );
        } catch (\Exception $e) {
            Log::error("User [{$user->id}] failed to switch user [{$userId}] to area [{$areaId}] because: " . $e->getMessage());
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }
}
