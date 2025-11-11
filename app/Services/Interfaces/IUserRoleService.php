<?php

namespace App\Services\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface IUserRoleService
{
    /**
     * Get all roles for a user with priorities
     *
     * @param int $userId
     * @return Collection
     */
    public function getUserRoles(int $userId): Collection;

    /**
     * Attach a role to a user with optional priority
     * If priority is null, auto-assigns next available priority
     *
     * @param int $userId
     * @param int $roleId
     * @param int|null $priority
     * @return void
     */
    public function attachRole(int $userId, int $roleId, ?int $priority = null): void;

    /**
     * Remove a role from a user and reorder remaining priorities
     *
     * @param int $userId
     * @param int $roleId
     * @return void
     */
    public function detachRole(int $userId, int $roleId): void;

    /**
     * Update the priority of a user's role and reorder if needed
     *
     * @param int $userId
     * @param int $roleId
     * @param int $priority
     * @return void
     */
    public function updateRolePriority(int $userId, int $roleId, int $priority): void;
}


