<?php

namespace App\Models\DTO;

use App\Enums\ScheduleType;
use App\Enums\ScheduleStatus;
use App\Enums\UserScheduleStatus;
use ReflectionClass;
use ReflectionProperty;

class ScheduleDTO
{
    public int $id;
    public string $name;
    public ?string $description;
    public ?string $local;
    public ?string $start_date;
    public ?string $end_date;
    public ?string $observation;
    public ScheduleType $type;
    public ?ScheduleStatus $status;
    public ?UserScheduleStatus $userStatus;
    public bool $minhaEscala;
    public string $created_at;


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
