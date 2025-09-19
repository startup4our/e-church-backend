<?php

namespace App\Http\Controllers;

use App\Exceptions\AppException;
use App\Enums\ErrorCode;
use App\Services\Interfaces\IRoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(private IRoleService $service) {}

    public function index()
    {
        try {
            $roles = $this->service->getAll();
            return response()->json([
                'success' => true,
                'data' => $roles
            ]);
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro interno do servidor'
            );
        }
    }

    public function show(int $id)
    {
        try {
            $role = $this->service->getById($id);
            return response()->json([
                'success' => true,
                'data' => $role
            ]);
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::RESOURCE_NOT_FOUND,
                userMessage: 'Função não encontrada'
            );
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name'        => ['required','string','max:120'],
                'description' => ['nullable','string','max:255'],
                'area_id'     => ['required','exists:area,id'],
            ]);

            $role = $this->service->create($data);
            return response()->json([
                'success' => true,
                'data' => $role
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

    public function update(Request $request, int $id)
    {
        try {
            $data = $request->validate([
                'name'        => ['sometimes','string','max:120'],
                'description' => ['nullable','string','max:255'],
                'area_id'     => ['sometimes','exists:area,id'],
            ]);

            $role = $this->service->update($id, $data);
            return response()->json([
                'success' => true,
                'data' => $role
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

    public function destroy(int $id)
    {
        try {
            $this->service->delete($id);
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
