<?php

namespace App\Http\Controllers;

use App\Exceptions\AppException;
use App\Enums\ErrorCode;
use App\Models\Permission;
use App\Services\Interfaces\IPermissionService;
use Illuminate\Http\Request;
use app\Http\Requests\PermissionRequest.php

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

    public function store(PermissionRequest $request)
    {
        try {
            $data = $request->validate();

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
            $data = $request->validate();

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
