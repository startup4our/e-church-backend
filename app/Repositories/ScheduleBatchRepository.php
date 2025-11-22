<?php

namespace App\Repositories;

use App\Models\ScheduleBatch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ScheduleBatchRepository
{
    /**
     * Cria batch
     */
    public function create(array $data): ScheduleBatch
    {
        try {
            return ScheduleBatch::create($data);
        } catch (\Exception $e) {
            Log::error("Erro ao criar batch", [
                'user_creator' => $data['user_creator'] ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Busca batch por ID
     */
    public function getById(int $id): ScheduleBatch
    {
        try {
            return ScheduleBatch::with('schedules', 'template', 'creator')
                ->findOrFail($id);
        } catch (\Exception $e) {
            Log::error("Erro ao buscar batch", [
                'batch_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Atualiza batch
     */
    public function update(int $id, array $data): ScheduleBatch
    {
        try {
            $batch = ScheduleBatch::findOrFail($id);
            $batch->update($data);
            
            return $batch->fresh();
        } catch (\Exception $e) {
            Log::error("Erro ao atualizar batch", [
                'batch_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Lista batches do usuário
     */
    public function getByUserId(int $userId): Collection
    {
        try {
            return ScheduleBatch::where('user_creator', $userId)
                ->with('template')
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error("Erro ao buscar batches do usuário", [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}

