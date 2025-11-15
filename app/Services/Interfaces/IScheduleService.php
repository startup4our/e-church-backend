<?php

namespace App\Services\Interfaces;

use App\Models\Schedule;
use Illuminate\Database\Eloquent\Collection;

interface IScheduleService
{
    public function create(array $data): Schedule;

    public function getAll(): Collection;

    public function getById(int $id): Schedule;

    public function update(int $id, array $data): Schedule;

    public function delete(int $id): bool;

    /**
     * Gera a escala automática para um schedule existente.
     *
     * @param int $scheduleId
     * @param array $areas Array de IDs de áreas
     * @param array $roleRequirements Array no formato [['role_id' => int, 'area_id' => int, 'count' => int], ...]
     * @return array ['users' => Collection, 'statistics' => array]
     */
    public function generateSchedule(int $scheduleId, array $areas, array $roleRequirements);

    /**
     * Publica uma escala, alterando seu status para ACTIVE e enviando notificações por email.
     *
     * @param int $scheduleId
     * @return Schedule
     */
    public function publish(int $scheduleId): Schedule;
}
