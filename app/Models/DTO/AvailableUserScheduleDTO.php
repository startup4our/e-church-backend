<?php

namespace App\Models\DTO;

use Illuminate\Support\Collection;
use ReflectionClass;
use ReflectionProperty;

class AvailableUserScheduleDTO
{
    public int $id;
    public string $name;
    public string $email;
    public ?string $photo_path;
    public ?string $birthday;
    // public ?UserScheduleStatus $statusSchedule;
    public Collection $areas;

    public function __construct($model)
    {
        $reflection = new ReflectionClass($this);
        $props = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);
        foreach ($props as $prop) {
            $propName = $prop->getName();
            $this->$propName = $model->$propName ?? null;
        }
    }
}
