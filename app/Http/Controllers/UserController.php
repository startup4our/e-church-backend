<?php

namespace App\Http\Controllers;

use App\Exceptions\AppException;
use App\Enums\ErrorCode;
use App\Enums\UserStatus;
use App\Models\User;
use App\Services\Interfaces\IPermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected IPermissionService $permissionService;

    public function __construct(IPermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    public function profile()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                throw new AppException(
                    ErrorCode::UNAUTHORIZED,
                    userMessage: 'Usuário não autenticado'
                );
            }

            // Load user relationships
            $user->load(['church', 'areas.area']);

            // Get user permissions
            $permissions = $this->permissionService->getUserPermissions($user->id);

            // Format the response
            $profileData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'photo_path' => $user->photo_path,
                'birthday' => $user->birthday,
                'status' => $user->status->value,
                'church' => $user->church ? [
                    'id' => $user->church->id,
                    'name' => $user->church->name,
                    'cep' => $user->church->cep,
                    'street' => $user->church->street,
                    'number' => $user->church->number,
                    'complement' => $user->church->complement,
                    'quarter' => $user->church->quarter,
                    'city' => $user->church->city,
                    'state' => $user->church->state,
                ] : null,
                'areas' => $user->areas->map(function ($userArea) {
                    return [
                        'id' => $userArea->area->id,
                        'name' => $userArea->area->name,
                        'description' => $userArea->area->description,
                    ];
                }),
                'permissions' => $permissions,
            ];

            return response()->json([
                'success' => true,
                'data' => $profileData
            ]);

        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro ao buscar perfil do usuário'
            );
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                throw new AppException(
                    ErrorCode::UNAUTHORIZED,
                    userMessage: 'Usuário não autenticado'
                );
            }

            $request->validate([
                'name' => 'sometimes|string|max:255',
                'photo_path' => 'sometimes|string|max:500',
                'birthday' => 'sometimes|date',
            ]);

            $user->update($request->only(['name', 'photo_path', 'birthday']));

            // Reload with relationships
            $user->load(['church', 'areas.area']);
            $permissions = $this->permissionService->getUserPermissions($user->id);

            $profileData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'photo_path' => $user->photo_path,
                'birthday' => $user->birthday,
                'status' => $user->status->value,
                'church' => $user->church ? [
                    'id' => $user->church->id,
                    'name' => $user->church->name,
                    'cep' => $user->church->cep,
                    'street' => $user->church->street,
                    'number' => $user->church->number,
                    'complement' => $user->church->complement,
                    'quarter' => $user->church->quarter,
                    'city' => $user->church->city,
                    'state' => $user->church->state,
                ] : null,
                'areas' => $user->areas->map(function ($userArea) {
                    return [
                        'id' => $userArea->area->id,
                        'name' => $userArea->area->name,
                        'description' => $userArea->area->description,
                    ];
                }),
                'permissions' => $permissions,
            ];

            return response()->json([
                'success' => true,
                'data' => $profileData,
                'message' => 'Perfil atualizado com sucesso'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                details: $e->errors()
            );
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro ao atualizar perfil do usuário'
            );
        }
    }

    public function getUsersByChurch()
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                throw new AppException(
                    ErrorCode::UNAUTHORIZED,
                    userMessage: 'Usuário não autenticado'
                );
            }

            // Get all users from the same church
            $users = User::with(['church', 'areas.area'])
                ->where('church_id', $user->church_id)
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'photo_path' => $user->photo_path,
                        'birthday' => $user->birthday,
                        'status' => $user->status->value,
                        'church' => $user->church ? [
                            'id' => $user->church->id,
                            'name' => $user->church->name,
                        ] : null,
                        'areas' => $user->areas->map(function ($userArea) {
                            return [
                                'id' => $userArea->area->id,
                                'name' => $userArea->area->name,
                                'description' => $userArea->area->description,
                            ];
                        }),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $users
            ]);

        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro ao buscar usuários da igreja'
            );
        }
    }

    public function updateUser(Request $request, string $id)
    {
        try {
            $authUser = Auth::user();
            
            if (!$authUser) {
                throw new AppException(
                    ErrorCode::UNAUTHORIZED,
                    userMessage: 'Usuário não autenticado'
                );
            }

            $user = User::where('id', $id)
                ->where('church_id', $authUser->church_id)
                ->first();

            if (!$user) {
                throw new AppException(
                    ErrorCode::RESOURCE_NOT_FOUND,
                    userMessage: 'Usuário não encontrado'
                );
            }

            $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|max:255|unique:users,email,' . $id,
                'photo_path' => 'sometimes|string|max:500',
                'birthday' => 'sometimes|date',
                'status' => 'sometimes|in:A,I,WA',
            ]);

            $user->update($request->only(['name', 'email', 'photo_path', 'birthday', 'status']));

            // Reload with relationships
            $user->load(['church', 'areas.area']);

            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'photo_path' => $user->photo_path,
                'birthday' => $user->birthday,
                'status' => $user->status->value,
                'church' => $user->church ? [
                    'id' => $user->church->id,
                    'name' => $user->church->name,
                ] : null,
                'areas' => $user->areas->map(function ($userArea) {
                    return [
                        'id' => $userArea->area->id,
                        'name' => $userArea->area->name,
                        'description' => $userArea->area->description,
                    ];
                }),
            ];

            return response()->json([
                'success' => true,
                'data' => $userData,
                'message' => 'Usuário atualizado com sucesso'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                details: $e->errors()
            );
        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro ao atualizar usuário'
            );
        }
    }

    public function toggleUserStatus(string $id)
    {
        try {
            $authUser = Auth::user();
            
            if (!$authUser) {
                throw new AppException(
                    ErrorCode::UNAUTHORIZED,
                    userMessage: 'Usuário não autenticado'
                );
            }

            $user = User::where('id', $id)
                ->where('church_id', $authUser->church_id)
                ->first();

            if (!$user) {
                throw new AppException(
                    ErrorCode::RESOURCE_NOT_FOUND,
                    userMessage: 'Usuário não encontrado'
                );
            }

            // Toggle between Active and Inactive (don't allow toggling from Waiting Approval)
            if ($user->status === UserStatus::ACTIVE) {
                $user->status = UserStatus::INACTIVE;
            } elseif ($user->status === UserStatus::INACTIVE) {
                $user->status = UserStatus::ACTIVE;
            } else {
                throw new AppException(
                    ErrorCode::VALIDATION_ERROR,
                    userMessage: 'Não é possível alterar o status de usuários aguardando aprovação'
                );
            }

            $user->save();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'status' => $user->status->value,
                ],
                'message' => 'Status do usuário atualizado com sucesso'
            ]);

        } catch (\Exception $e) {
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro ao alterar status do usuário'
            );
        }
    }
}
