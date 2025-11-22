<?php

namespace App\Events;

use App\Models\DTO\BulkScheduleCreateDTO;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BulkScheduleCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $batchId,
        public BulkScheduleCreateDTO $dto,
        public int $userId,
        public int $churchId
    ) {}
}

