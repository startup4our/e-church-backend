<?php

namespace App\Http\Controllers;

use App\Exceptions\AppException;
use App\Enums\ErrorCode;
use App\Models\Permission;
use App\Services\Interfaces\IPermissionService;
use App\Http\Requests\Auth\UpdatePermissionRequest;

class PermissionController extends Controller
{
    protected IPermissionService $service;

    public function __construct(IPermissionService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        try {
            $permissions = $this->service->listAll();
            return response()->json([
                'success' => true,
                'data' => $permissions
            ]);
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }

    public function store(UpdatePermissionRequest $request)
    {
        try {
            $permission = $this->service->create($request->validated());
            return response()->json([
                'success' => true,
                'data' => $permission
            ], 201);
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }

    public function show($id)
    {
        try {
            $permission = $this->service->get($id);
            return response()->json([
                'success' => true,
                'data' => $permission
            ]);
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::RESOURCE_NOT_FOUND,
                userMessage: 'Permissão não encontrada'
            );
        }
    }

    public function update(UpdatePermissionRequest $request, Permission $permission)
    {
        try {
            $updatedPermission = $this->service->update($permission, $request->validated());
            return response()->json([
                'success' => true,
                'data' => $updatedPermission
            ]);
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }

    public function destroy(Permission $permission)
    {
        try {
            $this->service->delete($permission);
            return response()->json([
                'success' => true,
                'data' => null
            ], 204);
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }

    public function updateByUserId(UpdatePermissionRequest $request, int $userId)
    {
        try {
            // Buscar permission pelo user_id
            $permission = Permission::where('user_id', $userId)->first();

            if (!$permission) {
                throw new AppException(
                    ErrorCode::RESOURCE_NOT_FOUND,
                    userMessage: 'Permissões do usuário não encontradas'
                );
            }

            $updatedPermission = $this->service->update($permission, $request->validated());

            return response()->json([
                'success' => true,
                'data' => $updatedPermission,
                'message' => 'Permissões atualizadas com sucesso'
            ]);
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro ao atualizar permissões'
            );
        }
    }
}
