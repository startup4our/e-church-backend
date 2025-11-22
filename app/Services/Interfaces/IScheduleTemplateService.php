<?php

namespace App\Services\Interfaces;

use App\Models\ScheduleTemplate;
use Illuminate\Support\Collection;

interface IScheduleTemplateService
{
    /**
     * Lista templates do usuário
     */
    public function getUserTemplates(int $userId): Collection;

    /**
     * Busca template por ID (valida user_id)
     */
    public function getTemplateById(int $id, int $userId): ScheduleTemplate;

    /**
     * Cria template
     */
    public function create(array $data, int $userId): ScheduleTemplate;

    /**
     * Atualiza template
     */
    public function update(int $id, array $data, int $userId): ScheduleTemplate;

    /**
     * Deleta template
     */
    public function delete(int $id, int $userId): bool;
}

