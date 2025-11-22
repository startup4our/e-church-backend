<?php

namespace App\Repositories;

use App\Models\ScheduleTemplate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ScheduleTemplateRepository
{
    /**
     * Lista templates do usuário
     */
    public function getByUserId(int $userId): Collection
    {
        try {
            return ScheduleTemplate::where('user_id', $userId)
                ->with('templateRoles.area', 'templateRoles.role')
                ->get();
        } catch (\Exception $e) {
            Log::error("Erro ao buscar templates do usuário", [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Lista templates do usuário que pertencem a áreas específicas
     */
    public function getByUserIdAndAreas(int $userId, array $areaIds): Collection
    {
        try {
            return ScheduleTemplate::where('user_id', $userId)
                ->whereHas('templateRoles', function ($query) use ($areaIds) {
                    $query->whereIn('area_id', $areaIds);
                })
                ->with('templateRoles.area', 'templateRoles.role')
                ->get()
                ->filter(function ($template) use ($areaIds) {
                    // Garantir que todas as áreas do template estão nas áreas do usuário
                    $templateAreaIds = $template->templateRoles->pluck('area_id')->unique()->toArray();
                    return count(array_intersect($templateAreaIds, $areaIds)) === count($templateAreaIds);
                });
        } catch (\Exception $e) {
            Log::error("Erro ao buscar templates do usuário por áreas", [
                'user_id' => $userId,
                'area_ids' => $areaIds,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Busca template do usuário por ID
     */
    public function getByIdAndUserId(int $id, int $userId): ScheduleTemplate
    {
        try {
            return ScheduleTemplate::where('id', $id)
                ->where('user_id', $userId)
                ->with('templateRoles.area', 'templateRoles.role')
                ->firstOrFail();
        } catch (\Exception $e) {
            Log::error("Erro ao buscar template", [
                'template_id' => $id,
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Busca template por ID (validação interna)
     */
    public function getById(int $id): ScheduleTemplate
    {
        try {
            return ScheduleTemplate::with('templateRoles.area', 'templateRoles.role')
                ->findOrFail($id);
        } catch (\Exception $e) {
            Log::error("Erro ao buscar template", [
                'template_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Cria template
     */
    public function create(array $data): ScheduleTemplate
    {
        try {
            return ScheduleTemplate::create($data);
        } catch (\Exception $e) {
            Log::error("Erro ao criar template", [
                'user_id' => $data['user_id'] ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Atualiza template
     */
    public function update(int $id, int $userId, array $data): ScheduleTemplate
    {
        try {
            $template = ScheduleTemplate::where('id', $id)
                ->where('user_id', $userId)
                ->firstOrFail();
            
            $template->update($data);
            
            return $template->fresh();
        } catch (\Exception $e) {
            Log::error("Erro ao atualizar template", [
                'template_id' => $id,
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Deleta template
     */
    public function delete(int $id, int $userId): bool
    {
        try {
            $template = ScheduleTemplate::where('id', $id)
                ->where('user_id', $userId)
                ->firstOrFail();
            
            return $template->delete();
        } catch (\Exception $e) {
            Log::error("Erro ao deletar template", [
                'template_id' => $id,
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}

