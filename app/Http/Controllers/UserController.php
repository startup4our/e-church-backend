<?php

namespace App\Http\Controllers;

use App\Exceptions\AppException;
use App\Enums\ErrorCode;
use App\Enums\UserStatus;
use App\Models\User;
use App\Services\Interfaces\IPermissionService;
use App\Services\Interfaces\IStorageService;
use App\Services\Interfaces\IUserRoleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    protected IPermissionService $permissionService;
    protected IStorageService $storageService;
    protected IUserRoleService $userRoleService;

    public function __construct(IPermissionService $permissionService, IStorageService $storageService, IUserRoleService $userRoleService)
    {
        $this->permissionService = $permissionService;
        $this->storageService = $storageService;
        $this->userRoleService = $userRoleService;
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
            $user->load(['church', 'areas.area', 'roles']);

            // Get user permissions
            $permissions = $this->permissionService->getUserPermissions($user->id);

            // Get signed URL for photo if exists
            $photoUrl = null;
            if ($user->photo_path) {
                try {
                $photoUrl = $this->storageService->getSignedUrl($user->photo_path, 60);
                } catch (\Exception $e) {
                    // Log error but don't fail the request
                    \Log::warning('Failed to generate signed URL for user photo', [
                        'user_id' => $user->id,
                        'photo_path' => $user->photo_path,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Format the response
            $profileData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'photo_path' => $user->photo_path,
                'photo_url' => $photoUrl, // Signed URL for immediate use
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
                'roles' => $user->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => $role->name,
                        'description' => $role->description ?? '',
                        'area_id' => $role->area_id,
                        'priority' => $role->pivot->priority ?? 1,
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

            // Get all users from the same church with areas and roles
            $users = User::with(['church', 'areas.area', 'roles'])
                ->where('church_id', $user->church_id)
                ->get()
                ->map(function ($user) {
                    // Get signed URL for photo if exists
                    $photoUrl = null;
                    if ($user->photo_path) {
                        try {
                            $photoUrl = $this->storageService->getSignedUrl($user->photo_path, 60);
                        } catch (\Exception $e) {
                            // Log error but don't fail the request
                            \Log::warning('Failed to generate signed URL for user photo', [
                                'user_id' => $user->id,
                                'photo_path' => $user->photo_path,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }

                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'photo_path' => $user->photo_path,
                        'photo_url' => $photoUrl, // Signed URL for immediate use
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
                        'roles' => $user->roles->map(function ($role) {
                            return [
                                'id' => $role->id,
                                'name' => $role->name,
                                'description' => $role->description ?? '',
                                'area_id' => $role->area_id,
                                'priority' => $role->pivot->priority ?? 1,
                            ];
                        }),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $users
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching users by church: ' . $e->getMessage());
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

    public function getRoles(int $id)
    {
        $user = Auth::user();
        
        // Check if user has permission to manage users
        if (!$this->permissionService->hasPermission($user->id, 'manage_users')) {
            Log::warning("User [{$user->id}] attempted to view roles for user [{$id}] without permission");
            throw new AppException(
                ErrorCode::PERMISSION_DENIED,
                userMessage: 'Você não tem permissão para visualizar funções dos usuários'
            );
        }

        Log::info("User [{$user->id}] requested to view roles for user [{$id}]");

        try {
            $roles = $this->userRoleService->getUserRoles($id);
            
            $rolesData = $roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'description' => $role->description ?? '',
                    'area_id' => $role->area_id,
                    'priority' => $role->pivot->priority ?? 1,
                ];
            });
            
            Log::info("User [{$user->id}] successfully retrieved " . $rolesData->count() . " roles for user [{$id}]");
            
            return response()->json([
                'success' => true,
                'data' => $rolesData
            ]);
        } catch (\Exception $e) {
            Log::error("User [{$user->id}] failed to retrieve roles for user [{$id}]: " . $e->getMessage());
            throw new AppException(
                ErrorCode::RESOURCE_NOT_FOUND,
                userMessage: 'Usuário não encontrado'
            );
        }
    }

    public function attachRole(Request $request, int $id)
    {
        $user = Auth::user();
        
        // Check if user has permission to manage users
        if (!$this->permissionService->hasPermission($user->id, 'manage_users')) {
            Log::warning("User [{$user->id}] attempted to attach role to user [{$id}] without permission");
            throw new AppException(
                ErrorCode::PERMISSION_DENIED,
                userMessage: 'Você não tem permissão para atribuir funções aos usuários'
            );
        }

        Log::info("User [{$user->id}] attempting to attach role to user [{$id}]");

        try {
            $data = $request->validate([
                'role_id' => 'required|integer|exists:role,id',
                'priority' => 'nullable|integer|min:1',
            ]);

            $this->userRoleService->attachRole($id, $data['role_id'], $data['priority'] ?? null);
            Log::info("User [{$user->id}] successfully attached role [{$data['role_id']}] to user [{$id}]");
            
            return response()->json([
                'success' => true,
                'message' => 'Função atribuída ao usuário com sucesso'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning("User [{$user->id}] failed to attach role to user [{$id}] because of validation errors: " . json_encode($e->errors()));
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                details: $e->errors()
            );
        } catch (AppException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("User [{$user->id}] failed to attach role to user [{$id}]: " . $e->getMessage());
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro ao atribuir função ao usuário'
            );
        }
    }

    public function detachRole(int $id, int $roleId)
    {
        $user = Auth::user();
        
        // Check if user has permission to manage users
        if (!$this->permissionService->hasPermission($user->id, 'manage_users')) {
            Log::warning("User [{$user->id}] attempted to detach role [{$roleId}] from user [{$id}] without permission");
            throw new AppException(
                ErrorCode::PERMISSION_DENIED,
                userMessage: 'Você não tem permissão para remover funções dos usuários'
            );
        }

        Log::info("User [{$user->id}] attempting to detach role [{$roleId}] from user [{$id}]");

        try {
            $this->userRoleService->detachRole($id, $roleId);
            Log::info("User [{$user->id}] successfully detached role [{$roleId}] from user [{$id}]");
            
            return response()->json([
                'success' => true,
                'message' => 'Função removida do usuário com sucesso'
            ]);
        } catch (AppException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("User [{$user->id}] failed to detach role [{$roleId}] from user [{$id}]: " . $e->getMessage());
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro ao remover função do usuário'
            );
        }
    }

    public function updateRolePriority(Request $request, int $id, int $roleId)
    {
        $user = Auth::user();
        
        // Check if user has permission to manage users
        if (!$this->permissionService->hasPermission($user->id, 'manage_users')) {
            Log::warning("User [{$user->id}] attempted to update role priority for user [{$id}] without permission");
            throw new AppException(
                ErrorCode::PERMISSION_DENIED,
                userMessage: 'Você não tem permissão para atualizar prioridades de funções'
            );
        }

        Log::info("User [{$user->id}] attempting to update role [{$roleId}] priority for user [{$id}]");

        try {
            $data = $request->validate([
                'priority' => 'required|integer|min:1',
            ]);

            $this->userRoleService->updateRolePriority($id, $roleId, $data['priority']);
            Log::info("User [{$user->id}] successfully updated role [{$roleId}] priority to [{$data['priority']}] for user [{$id}]");
            
            return response()->json([
                'success' => true,
                'message' => 'Prioridade da função atualizada com sucesso'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning("User [{$user->id}] failed to update role priority for user [{$id}] because of validation errors: " . json_encode($e->errors()));
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                details: $e->errors()
            );
        } catch (AppException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("User [{$user->id}] failed to update role priority for user [{$id}]: " . $e->getMessage());
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro ao atualizar prioridade da função'
            );
        }
    }

    public function updateFcmToken(Request $request)
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
                'fcm_token' => 'required|string',
            ]);

            $user->fcm_token = $request->fcm_token;
            $user->save();

            Log::info("FCM token updated for user", [
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'FCM token atualizado com sucesso'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw new AppException(
                ErrorCode::VALIDATION_ERROR,
                details: $e->errors()
            );
        } catch (\Exception $e) {
            Log::error("Failed to update FCM token: " . $e->getMessage());
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro ao atualizar FCM token'
            );
        }
    }
}
