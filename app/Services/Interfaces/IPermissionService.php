<?php

namespace App\Services\Interfaces;

use App\Models\Permission;

interface IPermissionService
{
    public function listAll();
    public function create(array $data): Permission;
    public function get(int $id): ?Permission;
    public function update(Permission $permission, array $data): Permission;
    public function delete(Permission $permission): void;

    /**
     * Verifica se o usuário tem permissão de criar escalas.
     *
     * @param int $userId
     * @return bool
     */
    public function canCreateScale(int $userId): bool;

    /**
     * Verifica permissões genéricas por campo.
     *
     * @param int $userId
     * @param string $permissionField
     * @return bool
     */
    public function hasPermission(int $userId, string $permissionField): bool;

    /**
     * Retorna todas as permissões de um usuário.
     *
     * @param int $userId
     * @return array
     */
    public function getUserPermissions(int $userId): array;
}
