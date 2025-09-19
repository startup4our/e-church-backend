<?php

namespace App\Http\Controllers;

use App\Exceptions\AppException;
use App\Enums\ErrorCode;
use App\Models\Permission;
use App\Services\Interfaces\IPermissionService;
use Illuminate\Http\Request;

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

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'user_id' => 'required|exists:users,id',
                'create_scale' => 'boolean',
                'read_scale' => 'boolean',
                'update_scale' => 'boolean',
                'delete_scale' => 'boolean',
                'create_music' => 'boolean',
                'read_music' => 'boolean',
                'update_music' => 'boolean',
                'delete_music' => 'boolean',
                'create_role' => 'boolean',
                'read_role' => 'boolean',
                'update_role' => 'boolean',
                'delete_role' => 'boolean',
                'create_area' => 'boolean',
                'read_area' => 'boolean',
                'update_area' => 'boolean',
                'delete_area' => 'boolean',
                'manage_users' => 'boolean',
                'manage_church_settings' => 'boolean',
                'manage_app_settings' => 'boolean',
            ]);

            $permission = $this->service->create($data);
            return response()->json([
                'success' => true,
                'data' => $permission
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                details: $e->errors()
            );
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

    public function update(Request $request, Permission $permission)
    {
        try {
            $data = $request->validate([
                'create_scale' => 'boolean',
                'read_scale' => 'boolean',
                'update_scale' => 'boolean',
                'delete_scale' => 'boolean',
                'create_music' => 'boolean',
                'read_music' => 'boolean',
                'update_music' => 'boolean',
                'delete_music' => 'boolean',
                'create_role' => 'boolean',
                'read_role' => 'boolean',
                'update_role' => 'boolean',
                'delete_role' => 'boolean',
                'create_area' => 'boolean',
                'read_area' => 'boolean',
                'update_area' => 'boolean',
                'delete_area' => 'boolean',
                'manage_users' => 'boolean',
                'manage_church_settings' => 'boolean',
                'manage_app_settings' => 'boolean',
            ]);

            $updatedPermission = $this->service->update($permission, $data);
            return response()->json([
                'success' => true,
                'data' => $updatedPermission
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                details: $e->errors()
            );
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
}
