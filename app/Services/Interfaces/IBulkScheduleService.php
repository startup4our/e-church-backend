<?php

namespace App\Services\Interfaces;

use App\Models\DTO\BulkScheduleCreateDTO;
use App\Models\ScheduleBatch;
use Illuminate\Support\Collection;

interface IBulkScheduleService
{
    /**
     * Inicia criação em massa (cria batch e dispara job)
     */
    public function createBulkSchedules(
        BulkScheduleCreateDTO $dto,
        int $userId,
        int $churchId
    ): ScheduleBatch;

    /**
     * Retorna status do batch
     */
    public function getBatchStatus(int $batchId, int $userId): ScheduleBatch;

    /**
     * Lista batches do usuário
     */
    public function getUserBatches(int $userId): Collection;
}

