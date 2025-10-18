<?php

namespace App\Helpers;

/**
 * Helper class to provide default permissions for new users
 * These are generic, non-sensitive permissions that any member should have
 * Maps to the actual boolean columns in the permission table
 */
class DefaultPermissionsHelper
{
    /**
     * Get default permissions for a new member user
     * Returns an array with column names as keys and boolean values
     * 
     * @return array<string, bool>
     */
    public static function getMemberPermissions(): array
    {
        return [
            // Read permissions (safe for regular members)
            'read_scale' => true,
            'read_music' => true,
            'read_role' => true,
            'read_area' => true,
            'read_chat' => true,
            
            // Creation/modification not allowed by default
            'create_scale' => false,
            'update_scale' => false,
            'delete_scale' => false,
            'create_music' => false,
            'update_music' => false,
            'delete_music' => false,
            'create_role' => false,
            'update_role' => false,
            'delete_role' => false,
            'create_area' => false,
            'update_area' => false,
            'delete_area' => false,
            'create_chat' => false,
            'update_chat' => false,
            'delete_chat' => false,
            
            // Management permissions
            'manage_users' => false,
            'manage_church_settings' => false,
            'manage_app_settings' => false,
        ];
    }

    /**
     * Get default permissions for an admin user
     * Returns an array with column names as keys and boolean values
     * 
     * @return array<string, bool>
     */
    public static function getAdminPermissions(): array
    {
        return [
            // Full CRUD permissions for admins
            'create_scale' => true,
            'read_scale' => true,
            'update_scale' => true,
            'delete_scale' => true,
            'create_music' => true,
            'read_music' => true,
            'update_music' => true,
            'delete_music' => true,
            'create_role' => true,
            'read_role' => true,
            'update_role' => true,
            'delete_role' => true,
            'create_area' => true,
            'read_area' => true,
            'update_area' => true,
            'delete_area' => true,
            'create_chat' => true,
            'read_chat' => true,
            'update_chat' => true,
            'delete_chat' => true,
            
            // Management permissions
            'manage_users' => true,
            'manage_church_settings' => true,
            'manage_app_settings' => true,
        ];
    }

    /**
     * Get permissions for a church leader (less than admin but more than member)
     * 
     * @return array<string, bool>
     */
    public static function getLeaderPermissions(): array
    {
        return [
            // Can create and manage schedules and music
            'create_scale' => true,
            'read_scale' => true,
            'update_scale' => true,
            'delete_scale' => false,
            'create_music' => true,
            'read_music' => true,
            'update_music' => true,
            'delete_music' => false,
            'read_role' => true,
            'read_area' => true,
            'read_chat' => true,
            'create_chat' => true,
            'update_chat' => true,
            'delete_chat' => false,
            
            // Can manage users in their area
            'manage_users' => true,
            'manage_church_settings' => false,
            'manage_app_settings' => false,
        ];
    }
}

