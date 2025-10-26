<?php

namespace App\Repositories;

use App\Models\Schedule;
use App\Models\Unavailability;
use App\Models\User;
use \App\Models\DateException;
use App\Models\UserSchedule;
use Illuminate\Database\Eloquent\Collection;

class ScheduleRepository
{
    protected $model;
    const MONTHLY_LIMIT = 4;
    const MIN_GAP_DAYS = 7;

    public function __construct(Schedule $schedule)
    {
        $this->model = $schedule;
    }

    public function create(array $data): Schedule
    {
        $data['approved'] = true; // Garantir que nova escala começa como aprovada
        return $this->model->create($data);
    }

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function getById(int $id): Schedule
    {
        return $this->model->findOrFail($id);
    }

    public function update(int $id, array $data): Schedule
    {
        $schedule = $this->model->findOrFail($id);
        $schedule->update($data);
        return $schedule;
    }

    public function delete(int $id): bool
    {
        $schedule = $this->model->findOrFail($id);
        return $schedule->delete();
    }

    public function generateSchedule(int $scheduleId, array $areas, int $maxUsers): Collection
    {
        $schedule = Schedule::findOrFail($scheduleId);
        $weekday = $schedule->start_date->dayOfWeek;

        // 1. Buscar usuários das áreas permitidas
        $users = User::query()
            ->join('user_area', 'users.id', '=', 'user_area.user_id')
            ->whereIn('user_area.area_id', $areas) // agora filtra pelo ID
            ->select('users.*')
            ->get();


        $userIds = $users->pluck('id');

        // 2. Pré-buscar exceções e indisponibilidades
        $exceptions = DateException::query()
            ->whereIn('user_id', $userIds)
            ->whereDate('exception_date', $schedule->start_date)
            ->pluck('user_id')
            ->toArray();

        $unavailabilities = Unavailability::query()
            ->whereIn('user_id', $userIds)
            ->where('weekday', $weekday)
            ->pluck('user_id')
            ->toArray();

        // 3. Filtrar usuários disponíveis
        $users = $users->filter(fn($user) =>
            !in_array($user->id, $exceptions) &&
            !in_array($user->id, $unavailabilities)
        );

        $filteredUserIds = $users->pluck('id');

        // 4. Histórico do mês e última escala
        $userSchedules = UserSchedule::query()
            ->whereIn('user_id', $filteredUserIds)
            ->whereMonth('created_at', $schedule->start_date->month)
            ->get()
            ->groupBy('user_id');

        $lastSchedules = UserSchedule::query()
            ->whereIn('user_id', $filteredUserIds)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('user_id');

        // 5. Aplicar regras de limite mensal e janela mínima
        $users = $users->filter(function($user) use ($userSchedules, $lastSchedules, $schedule) {
            $monthlyCount = isset($userSchedules[$user->id]) ? count($userSchedules[$user->id]) : 0;
            if ($monthlyCount >= self::MONTHLY_LIMIT) return false;

            $lastScheduleDate = isset($lastSchedules[$user->id]) ? $lastSchedules[$user->id][0]->schedule->start_date : null;
            if ($lastScheduleDate && $schedule->start_date->diffInDays($lastScheduleDate) < self::MIN_GAP_DAYS) {
                return false;
            }

            return true;
        });

        // 6. Ordenar pelo menos escalado
        $users = $users->sortBy(fn($user) => isset($userSchedules[$user->id]) ? count($userSchedules[$user->id]) : 0);

        // 7. Selecionar até maxUsers
        $selectedUsers = $users->take($maxUsers);

        // 8. Criar registros em user_schedule
        $userScheduleData = $selectedUsers->map(fn($user) => [
            'schedule_id' => $schedule->id,
            'user_id' => $user->id,
            'status' => 'confirmed', //default
        ])->toArray();

        UserSchedule::insert($userScheduleData); // Mais performático que create() em loop

        return $selectedUsers;
    }

}
