<?php

namespace App\Services;

use App\Enums\UserStatus;
use App\Helpers\DefaultPermissionsHelper;
use App\Models\Invite;
use App\Models\User;
use App\Models\Permission;
use App\Repositories\UserRepository;
use App\Services\Interfaces\IUserService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService implements IUserService
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function create(array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        
        return $this->repository->create($data);
    }

    public function getById(int $id): ?User
    {
        return $this->repository->getById($id);
    }

    public function update(int $id, array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    /**
     * Create a user from an invite with auto-approval
     */
    public function createByInvite(array $userData, Invite $invite): User
    {
        Log::info("Creating user from invite", ['invite_id' => $invite->id, 'email' => $invite->email]);

        // Create user with ACTIVE status (auto-approved)
        $user = $this->repository->create([
            'name' => $userData['name'],
            'email' => $invite->email,
            'password' => Hash::make($userData['password']),
            'birthday' => $userData['birthday'],
            'church_id' => $invite->church_id,
            'status' => UserStatus::ACTIVE, // Auto-approved
        ]);

        // Attach all areas from invite
        $areaIds = $invite->areas->pluck('id')->toArray();
        if (!empty($areaIds)) {
            $this->repository->attachAreas($user->id, $areaIds);
            Log::info("Attached areas to user", ['user_id' => $user->id, 'area_ids' => $areaIds]);
        }

        // Attach all roles from invite
        $roleIds = $invite->roles->pluck('id')->toArray();
        if (!empty($roleIds)) {
            $this->repository->attachRoles($user->id, $roleIds);
            Log::info("Attached roles to user", ['user_id' => $user->id, 'role_ids' => $roleIds]);
        }

        // Set default member permissions
        $this->setDefaultPermissions($user->id);

        return $user;
    }

    /**
     * Set default permissions for a user
     * Creates a single permission record with boolean flags for each permission type
     * 
     * @param int $userId
     * @param string $permissionLevel 'member'|'leader'|'admin'
     * @return void
     */
    protected function setDefaultPermissions(int $userId, string $permissionLevel = 'member'): void
    {
        // Get permission set based on level
        $permissions = match($permissionLevel) {
            'admin' => DefaultPermissionsHelper::getAdminPermissions(),
            'leader' => DefaultPermissionsHelper::getLeaderPermissions(),
            default => DefaultPermissionsHelper::getMemberPermissions(),
        };
        
        // Add user_id to the permissions array
        $permissions['user_id'] = $userId;
        
        // Create single permission record with all boolean flags
        Permission::create($permissions);
        
        Log::info("Set default permissions for user", [
            'user_id' => $userId, 
            'permission_level' => $permissionLevel
        ]);
    }
}

