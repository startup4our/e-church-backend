<?php

namespace App\Services;

use App\Models\Area;
use App\Repositories\AreaRepository;
use App\Repositories\ChatRepository;
use App\Repositories\UserAreaRepository;
use App\Services\Interfaces\IAreaService;
use App\Services\Interfaces\IRoleService;
use App\Enums\ChatType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AreaService implements IAreaService
{
    private AreaRepository $repository;
    private ChatRepository $chatRepository;
    private UserAreaRepository $userAreaRepository;
    private IRoleService $roleService;

    public function __construct(AreaRepository $repository, ChatRepository $chatRepository, UserAreaRepository $userAreaRepository, IRoleService $roleService)
    {
        $this->repository = $repository;
        $this->chatRepository = $chatRepository;
        $this->userAreaRepository = $userAreaRepository;
        $this->roleService = $roleService;
    }

    public function create(array $data): Area
    {
        Log::info("Creating new area with data: " . json_encode($data));
        
        try {
            return DB::transaction(function () use ($data) {
                // Extract roles if present
                $roles = $data['roles'] ?? [];
                unset($data['roles']);
                
                // Criar a área
                $area = $this->repository->create($data);
                Log::info("Area [{$area->id}] '{$area->name}' created successfully");
                
                // Create roles if provided
                if (!empty($roles)) {
                    foreach ($roles as $roleData) {
                        $roleData['area_id'] = $area->id;
                        $role = $this->roleService->create($roleData);
                        Log::info("Role [{$role->id}] '{$role->name}' created for area [{$area->id}]");
                    }
                }
                
                // Criar o chat padrão para a área
                $chatData = [
                    'name' => 'Chat Geral - ' . $area->name,
                    'description' => 'Chat geral da área ' . $area->name,
                    'chatable_id' => $area->id,
                    'chatable_type' => ChatType::AREA->value,
                ];

                $chat = $this->chatRepository->create($chatData);
                Log::info("Default chat created successfully for area [{$area->id}] with chat ID: {$chat->id}");

                return $area;
            });
        } catch (\Exception $e) {
            Log::error("Failed to create area: " . $e->getMessage());
            throw $e;
        }
    }

    public function getAll(): Collection
    {
        Log::info("Retrieving all areas");
        
        try {
            $areas = $this->repository->getAll();
            Log::info("Retrieved " . $areas->count() . " areas");
            return $areas;
        } catch (\Exception $e) {
            Log::error("Failed to retrieve areas: " . $e->getMessage());
            throw $e;
        }
    }

    public function getByChurchId(int $churchId): Collection
    {
        Log::info("Retrieving areas for church [{$churchId}]");
        
        try {
            $areas = $this->repository->getByChurchId($churchId);
            Log::info("Retrieved " . $areas->count() . " areas for church [{$churchId}]");
            return $areas;
        } catch (\Exception $e) {
            Log::error("Failed to retrieve areas for church [{$churchId}]: " . $e->getMessage());
            throw $e;
        }
    }

    public function getByChurchIdWithRoles(int $churchId): Collection
    {
        Log::info("Retrieving areas with roles for church [{$churchId}]");
        
        try {
            $areas = $this->repository->getByChurchIdWithRoles($churchId);
            Log::info("Retrieved " . $areas->count() . " areas with roles for church [{$churchId}]");
            return $areas;
        } catch (\Exception $e) {
            Log::error("Failed to retrieve areas with roles for church [{$churchId}]: " . $e->getMessage());
            throw $e;
        }
    }

    public function getById(int $id): Area
    {
        Log::info("Retrieving area with ID: {$id}");
        
        try {
            $area = $this->repository->getById($id);
            Log::info("Area [{$id}] retrieved successfully");
            return $area;
        } catch (\Exception $e) {
            Log::error("Failed to retrieve area [{$id}]: " . $e->getMessage());
            throw $e;
        }
    }

    public function getByIdAndChurchId(int $id, int $churchId): Area
    {
        Log::info("Retrieving area [{$id}] for church [{$churchId}]");
        
        try {
            $area = $this->repository->getByIdAndChurchId($id, $churchId);
            Log::info("Area [{$id}] retrieved successfully for church [{$churchId}]");
            return $area;
        } catch (\Exception $e) {
            Log::error("Failed to retrieve area [{$id}] for church [{$churchId}]: " . $e->getMessage());
            throw $e;
        }
    }

    public function update(int $id, array $data): Area
    {
        Log::info("Updating area [{$id}] with data: " . json_encode($data));
        
        try {
            $area = $this->repository->update($id, $data);
            Log::info("Area [{$id}] updated successfully");
            return $area;
        } catch (\Exception $e) {
            Log::error("Failed to update area [{$id}]: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateByIdAndChurchId(int $id, int $churchId, array $data): Area
    {
        Log::info("Attempting to update area [{$id}] details for church [{$churchId}] with new data: " . json_encode($data));
        
        try {
            $area = $this->repository->updateByIdAndChurchId($id, $churchId, $data);
            Log::info("Area [{$id}] '{$area->name}' details updated successfully for church [{$churchId}]");
            return $area;
        } catch (\Exception $e) {
            Log::error("Failed to update area [{$id}] details for church [{$churchId}] because: " . $e->getMessage());
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        Log::info("Deleting area with ID: {$id}");
        
        try {
            $result = $this->repository->delete($id);
            Log::info("Area [{$id}] deleted successfully");
            return $result;
        } catch (\Exception $e) {
            Log::error("Failed to delete area [{$id}]: " . $e->getMessage());
            throw $e;
        }
    }

    public function deleteByIdAndChurchId(int $id, int $churchId): bool
    {
        Log::info("Deleting area [{$id}] for church [{$churchId}]");
        
        try {
            // Check if area has associated users
            $userCount = $this->userAreaRepository->getUsersByAreaId($id)->count();
            if ($userCount > 0) {
                Log::warning("Cannot delete area [{$id}] - it has {$userCount} associated users");
                throw new \App\Exceptions\AppException(
                    \App\Enums\ErrorCode::AREA_HAS_USERS,
                    userMessage: "Não é possível excluir uma área que possui usuários associados. Remova os usuários da área primeiro."
                );
            }
            
            $result = $this->repository->deleteByIdAndChurchId($id, $churchId);
            Log::info("Area [{$id}] deleted successfully for church [{$churchId}]");
            return $result;
        } catch (\App\Exceptions\AppException $e) {
            // Re-throw AppException as-is
            throw $e;
        } catch (\Exception $e) {
            Log::error("Failed to delete area [{$id}] for church [{$churchId}]: " . $e->getMessage());
            throw $e;
        }
    }

    public function getUserAreas(int $user_id): Collection|Area
    {
        Log::info("Retrieving areas for user [{$user_id}]");
        $areas = $this->repository->getUserArea($user_id);
        Log::info("Retrieved " . $areas->count() . " areas for user [{$user_id}]");
        return $areas;
    }

    public function getUsersByAreaId(int $areaId, int $churchId): Collection
    {
        Log::info("Retrieving users for area [{$areaId}] in church [{$churchId}]");
        
        try {
            // First verify the area belongs to the church
            $area = $this->repository->getByIdAndChurchId($areaId, $churchId);
            
            // Get users from the area
            $userAreas = $this->userAreaRepository->getUsersByAreaId($areaId);
            $users = $userAreas->map(function ($userArea) {
                return [
                    'id' => $userArea->user->id,
                    'name' => $userArea->user->name,
                    'email' => $userArea->user->email,
                    'status' => $userArea->user->status->value,
                    'photo_path' => $userArea->user->photo_path,
                    'birthday' => $userArea->user->birthday,
                ];
            });
            
            Log::info("Retrieved " . $users->count() . " users from area [{$areaId}] in church [{$churchId}]");
            return $users;
        } catch (\Exception $e) {
            Log::error("Failed to retrieve users from area [{$areaId}] in church [{$churchId}]: " . $e->getMessage());
            throw $e;
        }
    }

    public function switchUserArea(int $userId, int $currentAreaId, int $newAreaId, int $churchId): void
    {
        Log::info("Switching user [{$userId}] from area [{$currentAreaId}] to area [{$newAreaId}] in church [{$churchId}]");
        
        try {
            DB::transaction(function () use ($userId, $currentAreaId, $newAreaId, $churchId) {
                // Verify both areas belong to the church
                $currentArea = $this->repository->getByIdAndChurchId($currentAreaId, $churchId);
                $newArea = $this->repository->getByIdAndChurchId($newAreaId, $churchId);
                
                // Remove user from current area
                $this->userAreaRepository->removeUserFromArea($userId, $currentAreaId);
                
                Log::info("User [{$userId}] removed from area [{$currentAreaId}]");
                
                // Add user to new area
                $this->userAreaRepository->addUserToArea($userId, $newAreaId);
                
                Log::info("User [{$userId}] added to area [{$newAreaId}]");
            });
            
            Log::info("User [{$userId}] successfully switched from area [{$currentAreaId}] to area [{$newAreaId}]");
        } catch (\Exception $e) {
            Log::error("Failed to switch user [{$userId}] from area [{$currentAreaId}] to area [{$newAreaId}]: " . $e->getMessage());
            throw $e;
        }
    }

    public function addUserToArea(int $userId, int $areaId, int $churchId): void
    {
        Log::info("Adding user [{$userId}] to area [{$areaId}] in church [{$churchId}]");
        
        try {
            // Verify the area belongs to the church
            $this->repository->getByIdAndChurchId($areaId, $churchId);
            
            // Check if user is already in this area
            if ($this->userAreaRepository->userBelongsToArea($userId, $areaId)) {
                Log::warning("User [{$userId}] is already associated with area [{$areaId}]");
                throw new \App\Exceptions\AppException(
                    \App\Enums\ErrorCode::VALIDATION_ERROR,
                    userMessage: 'Usuário já está associado a esta área'
                );
            }
            
            // Add user to area
            $this->userAreaRepository->addUserToArea($userId, $areaId);
            Log::info("User [{$userId}] successfully added to area [{$areaId}]");
            
        } catch (\App\Exceptions\AppException $e) {
            // Re-throw AppException as-is
            throw $e;
        } catch (\Exception $e) {
            Log::error("Failed to add user [{$userId}] to area [{$areaId}]: " . $e->getMessage());
            throw $e;
        }
    }

    public function removeUserFromArea(int $userId, int $areaId, int $churchId): void
    {
        Log::info("Removing user [{$userId}] from area [{$areaId}] in church [{$churchId}]");
        
        try {
            // Verify the area belongs to the church
            $this->repository->getByIdAndChurchId($areaId, $churchId);
            
            // Remove user from area
            $deleted = $this->userAreaRepository->removeUserFromArea($userId, $areaId);
            
            if (!$deleted) {
                Log::warning("User [{$userId}] was not associated with area [{$areaId}]");
                throw new \App\Exceptions\AppException(
                    \App\Enums\ErrorCode::RESOURCE_NOT_FOUND,
                    userMessage: 'Usuário não está associado a esta área'
                );
            }
            
            Log::info("User [{$userId}] successfully removed from area [{$areaId}]");
            
        } catch (\App\Exceptions\AppException $e) {
            // Re-throw AppException as-is
            throw $e;
        } catch (\Exception $e) {
            Log::error("Failed to remove user [{$userId}] from area [{$areaId}]: " . $e->getMessage());
            throw $e;
        }
    }

    public function getRolesByAreaId(int $areaId, int $churchId): Collection
    {
        Log::info("Retrieving roles for area [{$areaId}] in church [{$churchId}]");
        
        try {
            // Verify area belongs to church
            $this->repository->getByIdAndChurchId($areaId, $churchId);
            
            // Get roles for area
            $roles = $this->roleService->getByAreaId($areaId);
            
            Log::info("Retrieved " . $roles->count() . " roles for area [{$areaId}] in church [{$churchId}]");
            return $roles;
        } catch (\Exception $e) {
            Log::error("Failed to retrieve roles for area [{$areaId}] in church [{$churchId}]: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateAreaRoles(int $areaId, int $churchId, array $roles): void
    {
        Log::info("Updating roles for area [{$areaId}] in church [{$churchId}] with " . count($roles) . " roles");
        
        try {
            DB::transaction(function () use ($areaId, $churchId, $roles) {
                // Verify area belongs to church
                $area = $this->repository->getByIdAndChurchId($areaId, $churchId);
                
                // Get existing role IDs
                $existingRoles = $this->roleService->getByAreaId($areaId);
                $existingRoleIds = $existingRoles->pluck('id')->toArray();
                
                // Process roles array
                $newRoleIds = [];
                foreach ($roles as $roleData) {
                    if (isset($roleData['id'])) {
                        // Update existing role
                        $this->roleService->update($roleData['id'], [
                            'name' => $roleData['name'],
                            'description' => $roleData['description'] ?? null,
                        ]);
                        $newRoleIds[] = $roleData['id'];
                        Log::info("Updated role [{$roleData['id']}] '{$roleData['name']}' for area [{$areaId}]");
                    } else {
                        // Create new role
                        $newRole = $this->roleService->create([
                            'name' => $roleData['name'],
                            'description' => $roleData['description'] ?? null,
                            'area_id' => $areaId,
                        ]);
                        $newRoleIds[] = $newRole->id;
                        Log::info("Created role [{$newRole->id}] '{$newRole->name}' for area [{$areaId}]");
                    }
                }
                
                // Delete roles not in the new list
                $rolesToDelete = array_diff($existingRoleIds, $newRoleIds);
                foreach ($rolesToDelete as $roleId) {
                    $this->roleService->delete($roleId);
                    Log::info("Deleted role [{$roleId}] from area [{$areaId}]");
                }
                
                Log::info("Successfully updated roles for area [{$areaId}] in church [{$churchId}]");
            });
        } catch (\Exception $e) {
            Log::error("Failed to update roles for area [{$areaId}] in church [{$churchId}]: " . $e->getMessage());
            throw $e;
        }
    }

}