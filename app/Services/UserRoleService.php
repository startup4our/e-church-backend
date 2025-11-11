<?php

namespace App\Services;

use App\Exceptions\AppException;
use App\Enums\ErrorCode;
use App\Models\User;
use App\Models\Role;
use App\Repositories\UserAreaRepository;
use App\Services\Interfaces\IUserRoleService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserRoleService implements IUserRoleService
{
    protected UserAreaRepository $userAreaRepository;

    public function __construct(UserAreaRepository $userAreaRepository)
    {
        $this->userAreaRepository = $userAreaRepository;
    }

    public function getUserRoles(int $userId): Collection
    {
        Log::info("Retrieving roles for user [{$userId}]");

        try {
            $user = User::findOrFail($userId);
            $roles = $user->roles()->get();
            
            Log::info("Retrieved " . $roles->count() . " roles for user [{$userId}]");
            return $roles;
        } catch (\Exception $e) {
            Log::error("Failed to retrieve roles for user [{$userId}]: " . $e->getMessage());
            throw $e;
        }
    }

    public function attachRole(int $userId, int $roleId, ?int $priority = null): void
    {
        Log::info("Attaching role [{$roleId}] to user [{$userId}] with priority [" . ($priority ?? 'auto') . "]");

        try {
            DB::transaction(function () use ($userId, $roleId, $priority) {
                $user = User::findOrFail($userId);
                $role = Role::findOrFail($roleId);

                // Check if role is already assigned
                if ($user->roles()->where('role_id', $roleId)->exists()) {
                    Log::warning("Role [{$roleId}] is already assigned to user [{$userId}]");
                    throw new AppException(
                        ErrorCode::ROLE_ALREADY_ASSIGNED,
                        userMessage: 'Esta função já está atribuída ao usuário'
                    );
                }

                // Validate that user belongs to the role's area
                if (!$this->userAreaRepository->userBelongsToArea($userId, $role->area_id)) {
                    Log::warning("User [{$userId}] attempted to assign role [{$roleId}] from area [{$role->area_id}] but doesn't belong to that area");
                    throw new AppException(
                        ErrorCode::ROLE_AREA_MISMATCH,
                        userMessage: 'O usuário não pertence à área desta função. Adicione o usuário à área primeiro.'
                    );
                }

                // Auto-assign priority if not provided
                if ($priority === null) {
                    $maxPriority = $user->roles()->max('role_user.priority') ?? 0;
                    $priority = $maxPriority + 1;
                    Log::info("Auto-assigned priority [{$priority}] for role [{$roleId}] to user [{$userId}]");
                }

                // Attach role with priority
                $user->roles()->attach($roleId, ['priority' => $priority]);
                
                Log::info("Successfully attached role [{$roleId}] to user [{$userId}] with priority [{$priority}]");
            });
        } catch (AppException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Failed to attach role [{$roleId}] to user [{$userId}]: " . $e->getMessage());
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro ao atribuir função ao usuário'
            );
        }
    }

    public function detachRole(int $userId, int $roleId): void
    {
        Log::info("Detaching role [{$roleId}] from user [{$userId}]");

        try {
            DB::transaction(function () use ($userId, $roleId) {
                $user = User::findOrFail($userId);

                // Check if role is assigned
                if (!$user->roles()->where('role_id', $roleId)->exists()) {
                    Log::warning("Role [{$roleId}] is not assigned to user [{$userId}]");
                    throw new AppException(
                        ErrorCode::ROLE_NOT_FOUND,
                        userMessage: 'Esta função não está atribuída ao usuário'
                    );
                }

                // Get priority of role being removed
                $removedPriority = $user->roles()
                    ->where('role_id', $roleId)
                    ->first()
                    ->pivot
                    ->priority;

                // Detach role
                $user->roles()->detach($roleId);

                // Reorder remaining priorities
                $this->reorderPriorities($userId, $removedPriority);

                Log::info("Successfully detached role [{$roleId}] from user [{$userId}] and reordered priorities");
            });
        } catch (AppException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Failed to detach role [{$roleId}] from user [{$userId}]: " . $e->getMessage());
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro ao remover função do usuário'
            );
        }
    }

    public function updateRolePriority(int $userId, int $roleId, int $priority): void
    {
        Log::info("Updating priority of role [{$roleId}] for user [{$userId}] to [{$priority}]");

        try {
            DB::transaction(function () use ($userId, $roleId, $priority) {
                $user = User::findOrFail($userId);

                // Check if role is assigned
                $userRole = $user->roles()->where('role_id', $roleId)->first();
                if (!$userRole) {
                    Log::warning("Role [{$roleId}] is not assigned to user [{$userId}]");
                    throw new AppException(
                        ErrorCode::ROLE_NOT_FOUND,
                        userMessage: 'Esta função não está atribuída ao usuário'
                    );
                }

                // Get total roles count for validation
                $totalRoles = $user->roles()->count();
                
                // Validate priority range
                if ($priority < 1) {
                    Log::warning("Invalid priority [{$priority}] provided for role [{$roleId}] and user [{$userId}]. Priority must be >= 1");
                    throw new AppException(
                        ErrorCode::INVALID_ROLE_PRIORITY,
                        userMessage: 'A prioridade deve ser um número positivo (mínimo: 1)'
                    );
                }
                
                if ($priority > $totalRoles) {
                    Log::warning("Invalid priority [{$priority}] provided for role [{$roleId}] and user [{$userId}]. Valid range: 1-{$totalRoles}");
                    throw new AppException(
                        ErrorCode::INVALID_ROLE_PRIORITY,
                        userMessage: "A prioridade deve estar entre 1 e {$totalRoles} (total de funções do usuário)"
                    );
                }

                $oldPriority = $userRole->pivot->priority;
                
                Log::info("Validated priority [{$priority}] for user [{$userId}] with {$totalRoles} total roles. Old priority: [{$oldPriority}]");

                // Update priority
                $user->roles()->updateExistingPivot($roleId, ['priority' => $priority]);

                // Reorder priorities if needed
                if ($oldPriority != $priority) {
                    $this->reorderPriorities($userId, $oldPriority, $priority);
                }

                Log::info("Successfully updated priority of role [{$roleId}] for user [{$userId}] from [{$oldPriority}] to [{$priority}]");
            });
        } catch (AppException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Failed to update priority of role [{$roleId}] for user [{$userId}]: " . $e->getMessage());
            throw new AppException(
                ErrorCode::INTERNAL_SERVER_ERROR,
                userMessage: 'Erro ao atualizar prioridade da função'
            );
        }
    }

    /**
     * Reorder priorities to ensure sequential ordering (1, 2, 3, ...)
     *
     * @param int $userId
     * @param int $removedPriority Priority that was removed or changed
     * @param int|null $newPriority New priority if updating (null if removing)
     * @return void
     */
    private function reorderPriorities(int $userId, int $removedPriority, ?int $newPriority = null): void
    {
        $user = User::findOrFail($userId);
        
        // Get all roles ordered by current priority
        $roles = $user->roles()->orderBy('role_user.priority', 'asc')->get();

        // Reassign sequential priorities starting from 1
        $sequentialPriority = 1;
        foreach ($roles as $role) {
            $currentPriority = $role->pivot->priority;
            
            // Skip if this is the role being updated and we're updating (not removing)
            if ($newPriority !== null && $currentPriority == $removedPriority) {
                // This role will get the new priority, continue to next
                continue;
            }
            
            // Assign sequential priority
            if ($currentPriority != $sequentialPriority) {
                $user->roles()->updateExistingPivot($role->id, ['priority' => $sequentialPriority]);
            }
            $sequentialPriority++;
        }

        // If updating, set the new priority for the updated role
        if ($newPriority !== null) {
            $updatedRole = $roles->firstWhere('pivot.priority', $removedPriority);
            if ($updatedRole) {
                $user->roles()->updateExistingPivot($updatedRole->id, ['priority' => $newPriority]);
            }
            
            // Final pass: ensure all priorities are sequential after the update
            $roles = $user->roles()->orderBy('role_user.priority', 'asc')->get();
            $finalPriority = 1;
            foreach ($roles as $role) {
                if ($role->pivot->priority != $finalPriority) {
                    $user->roles()->updateExistingPivot($role->id, ['priority' => $finalPriority]);
                }
                $finalPriority++;
            }
        }

        Log::info("Reordered priorities for user [{$userId}]");
    }
}

