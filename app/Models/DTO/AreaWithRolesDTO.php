<?php

namespace App\Models\DTO;

use App\Models\Area;

/**
 * DTO for Area with its associated Roles
 * Used to return areas with their roles in a single API call
 */
class AreaWithRolesDTO
{
    public int $id;
    public string $name;
    public string $description;
    public array $roles;

    public function __construct(Area $area)
    {
        $this->id = $area->id;
        $this->name = $area->name;
        $this->description = $area->description ?? '';
        
        // Load roles and format them
        $this->roles = $area->roles->map(function ($role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'description' => $role->description ?? '',
            ];
        })->toArray();
    }

    /**
     * Convert DTO to array for JSON response
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'roles' => $this->roles,
        ];
    }
}

