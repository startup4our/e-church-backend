<?php

namespace App\Models\DTO;

use App\Enums\UserScheduleStatus;
use ReflectionClass;
use ReflectionProperty;

class UserScheduleDetailsDTO
{
    public int $id;
    public string $name;
    public string $email;
    public ?string $photo_path;
    public ?string $photo_url; // Signed URL for immediate use
    public ?string $birthday;
    public ?UserScheduleStatus $statusSchedule;
    public ?string $area;
    public ?string $role;

    public function __construct($schedule)
    {
        $reflection = new ReflectionClass($this);
        $props = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
        foreach ($props as $prop) {
            $propName = $prop->getName();
            $this->$propName = $schedule->$propName;
        }
    }
}
