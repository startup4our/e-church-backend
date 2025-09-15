<?php

namespace App\Repositories;

use App\Models\Permission;

class PermissionRepository
{
    public function all()
    {
        return Permission::with('user')->get();
    }

    public function findById(int $id): ?Permission
    {
        return Permission::with('user')->find($id);
    }

    public function create(array $data): Permission
    {
        return Permission::create($data);
    }

    public function update(Permission $permission, array $data): Permission
    {
        $permission->update($data);
        return $permission;
    }

    public function delete(Permission $permission): void
    {
        $permission->delete();
    }

    public function getPermissionsByUser(int $userId): array
    {
        $permission = Permission::query()
            ->where('user_id', $userId)
            ->first();

        if (!$permission) {
            return [];
        }

        // Retorna todas as colunas de permissão como array associativo
        return $permission->only([
            'create_scale',
            'read_scale',
            'update_scale',
            'delete_scale',
            'create_music',
            'read_music',
            'update_music',
            'delete_music',
            'create_role',
            'read_role',
            'update_role',
            'delete_role',
            'create_area',
            'read_area',
            'update_area',
            'delete_area',
            'manage_users',
            'create_chat',
            'read_chat',
            'update_chat',
            'delete_chat',
            'manage_church_settings',
            'manage_app_settings',
        ]);
    }
}
