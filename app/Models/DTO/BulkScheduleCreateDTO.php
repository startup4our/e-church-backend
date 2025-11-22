<?php

namespace App\Models\DTO;

use App\Enums\RecurrenceType;
use App\Enums\ScheduleType;
use Carbon\Carbon;

class BulkScheduleCreateDTO
{
    public function __construct(
        public int $quantity,
        public string $nameBase,
        public ScheduleType $type,
        public string $description,
        public string $local,
        public string $startTime,
        public string $endTime,
        public RecurrenceType $recurrence,
        public array $areas,
        public array $roleRequirements,
        public bool $autoFill,
        public Carbon $startDate,
        public ?int $templateId = null,
        public ?int $musicTemplateId = null
    ) {}
}

