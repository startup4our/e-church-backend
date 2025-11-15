<?php

namespace App\Repositories;

use App\Models\Schedule;
use App\Models\Unavailability;
use App\Models\User;
use \App\Models\DateException;
use App\Models\UserSchedule;
use App\Enums\ScheduleStatus;
use App\Enums\UserScheduleStatus;
use App\Helpers\ScheduleHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

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
        if (!isset($data['status'])) {
            $data['status'] = ScheduleStatus::DRAFT->value;
        }
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

    /**
     * Gera escala automática dividida em etapas claras
     * 
     * @param int $scheduleId
     * @param array $areas Array de IDs de áreas
     * @param array $roleRequirements Array no formato [['role_id' => int, 'area_id' => int, 'count' => int], ...]
     * @return array ['users' => Collection, 'statistics' => array]
     */
    public function generateSchedule(int $scheduleId, array $areas, array $roleRequirements): array
    {
        Log::info("Starting automatic schedule generation for schedule [{$scheduleId}]", [
            'areas' => $areas,
            'role_requirements' => $roleRequirements
        ]);

        $schedule = Schedule::findOrFail($scheduleId);
        
        // Validar que a escala está em status Draft
        if ($schedule->status !== ScheduleStatus::DRAFT) {
            throw new \App\Exceptions\AppException(
                \App\Enums\ErrorCode::VALIDATION_ERROR,
                userMessage: 'Apenas escalas em status Rascunho podem ser geradas automaticamente'
            );
        }
        
        // Remover participantes existentes antes de gerar novos
        UserSchedule::where('schedule_id', $scheduleId)->delete();
        Log::info("Removed existing participants from schedule [{$scheduleId}]");
        
        // 1. Buscar candidatos elegíveis
        $eligibleUsers = $this->getEligibleUsers($schedule, $areas, $roleRequirements);
        Log::info("Found {$eligibleUsers->count()} eligible users");

        // 2. Filtrar por disponibilidade
        $availableUsers = $this->filterByAvailability($eligibleUsers, $schedule);
        Log::info("After availability filter: {$availableUsers->count()} users");

        // 3. Filtrar por regras de negócio (limite mensal, janela mínima)
        $validUsers = $this->filterByBusinessRules($availableUsers, $schedule);
        Log::info("After business rules filter: {$validUsers->count()} users");

        // 4. Selecionar participantes por função
        $selectedUsers = $this->selectUsersByRoles($validUsers, $roleRequirements, $schedule);
        Log::info("Selected {$selectedUsers->count()} users for schedule");

        // 5. Calcular estatísticas
        $statistics = $this->calculateStatistics($selectedUsers, $roleRequirements);
        Log::info("Generation statistics", ['statistics' => $statistics]);

        // 6. Criar registros na escala
        $this->assignUsersToSchedule($schedule, $selectedUsers, $roleRequirements);

        // 7. Atualizar status da escala para Draft
        $this->setScheduleStatus($schedule, ScheduleStatus::DRAFT);
        Log::info("Schedule [{$scheduleId}] status set to DRAFT");

        return [
            'users' => $selectedUsers,
            'statistics' => $statistics
        ];
    }

    /**
     * Calcula estatísticas da geração
     */
    private function calculateStatistics(Collection $selectedUsers, array $roleRequirements): array
    {
        $statistics = [];

        foreach ($roleRequirements as $req) {
            $roleId = $req['role_id'];
            $areaId = $req['area_id'];
            $requested = $req['count'];

            // Contar quantos usuários foram selecionados para essa função específica
            $selected = $selectedUsers->filter(function($user) use ($roleId, $areaId) {
                return isset($user->selected_for_role_id) && 
                       isset($user->selected_for_area_id) &&
                       $user->selected_for_role_id == $roleId &&
                       $user->selected_for_area_id == $areaId;
            })->count();

            $statistics[] = [
                'role_id' => $roleId,
                'area_id' => $areaId,
                'requested' => $requested,
                'selected' => $selected,
                'fulfilled' => $selected >= $requested
            ];
        }

        return $statistics;
    }

    /**
     * Busca usuários elegíveis (áreas + funções)
     */
    private function getEligibleUsers(Schedule $schedule, array $areas, array $roleRequirements): Collection
    {
        // Extrair role_ids únicos dos requisitos
        $roleIds = array_unique(array_column($roleRequirements, 'role_id'));

        // Buscar usuários das áreas que têm as funções necessárias
        $users = User::query()
            ->join('user_area', 'users.id', '=', 'user_area.user_id')
            ->join('role_user', 'users.id', '=', 'role_user.user_id')
            ->whereIn('user_area.area_id', $areas)
            ->whereIn('role_user.role_id', $roleIds)
            ->select('users.*')
            ->distinct()
            ->get();

        return $users;
    }

    /**
     * Filtra usuários por disponibilidade (exceções + indisponibilidades)
     */
    private function filterByAvailability(Collection $users, Schedule $schedule): Collection
    {
        $userIds = $users->pluck('id')->toArray();
        $weekday = $schedule->start_date->dayOfWeek;
        
        // Inferir turno da escala
        $shift = ScheduleHelper::inferShiftFromDateTime($schedule->start_date);
        $shiftDateException = ScheduleHelper::mapShiftToDateExceptionFormat($shift);

        // Buscar exceções de data (considerando período completo)
        $exceptions = DateException::query()
            ->whereIn('user_id', $userIds)
            ->whereBetween('exception_date', [
                $schedule->start_date->format('Y-m-d'),
                $schedule->end_date->format('Y-m-d')
            ])
            ->where('shift', $shiftDateException)
            ->pluck('user_id')
            ->toArray();

        // Buscar indisponibilidades semanais
        $unavailabilities = Unavailability::query()
            ->whereIn('user_id', $userIds)
            ->where('weekday', $weekday)
            ->where('shift', $shift)
            ->pluck('user_id')
            ->toArray();

        // Filtrar usuários disponíveis
        $availableUsers = $users->filter(function($user) use ($exceptions, $unavailabilities) {
            return !in_array($user->id, $exceptions) && !in_array($user->id, $unavailabilities);
        });

        return $availableUsers;
    }

    /**
     * Filtra por regras de negócio (limite mensal, janela mínima)
     */
    private function filterByBusinessRules(Collection $users, Schedule $schedule): Collection
    {
        $userIds = $users->pluck('id')->toArray();

        // Histórico do mês (excluindo a escala atual que está sendo regenerada)
        $userSchedules = UserSchedule::query()
            ->whereIn('user_id', $userIds)
            ->where('schedule_id', '!=', $schedule->id) // Excluir escala atual
            ->whereMonth('created_at', $schedule->start_date->month)
            ->whereYear('created_at', $schedule->start_date->year)
            ->get()
            ->groupBy('user_id');

        // Última escala de cada usuário (excluindo a escala atual)
        $lastSchedules = UserSchedule::query()
            ->whereIn('user_id', $userIds)
            ->where('schedule_id', '!=', $schedule->id) // Excluir escala atual
            ->with('schedule')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('user_id');

        // Aplicar filtros
        $validUsers = $users->filter(function($user) use ($userSchedules, $lastSchedules, $schedule) {
            // Limite mensal
            $monthlyCount = isset($userSchedules[$user->id]) ? count($userSchedules[$user->id]) : 0;
            if ($monthlyCount >= self::MONTHLY_LIMIT) {
                return false;
            }

            // Janela mínima
            if (isset($lastSchedules[$user->id]) && count($lastSchedules[$user->id]) > 0) {
                $lastSchedule = $lastSchedules[$user->id][0];
                if ($lastSchedule->schedule && $schedule->start_date->diffInDays($lastSchedule->schedule->start_date) < self::MIN_GAP_DAYS) {
                    return false;
                }
            }

            return true;
        });

        return $validUsers;
    }

    /**
     * Seleciona usuários por função respeitando quantidade por função
     */
    private function selectUsersByRoles(Collection $users, array $roleRequirements, Schedule $schedule): Collection
    {
        $selectedUsers = collect();
        $userIds = $users->pluck('id')->toArray();

        // Carregar histórico de escalas para ordenação (excluindo a escala atual)
        $userSchedules = UserSchedule::query()
            ->whereIn('user_id', $userIds)
            ->where('schedule_id', '!=', $schedule->id) // Excluir escala atual
            ->whereMonth('created_at', $schedule->start_date->month)
            ->whereYear('created_at', $schedule->start_date->year)
            ->get()
            ->groupBy('user_id');

        // Carregar funções dos usuários com prioridade
        $usersWithRoles = User::query()
            ->whereIn('id', $userIds)
            ->with(['roles' => function($query) {
                $query->orderBy('role_user.priority', 'asc');
            }])
            ->get();

        // Para cada requisito de função
        foreach ($roleRequirements as $index => $requirement) {
            $roleId = $requirement['role_id'];
            $areaId = $requirement['area_id'];
            $count = $requirement['count'];

            // Filtrar usuários que têm essa função na área correta
            $candidates = $usersWithRoles->filter(function($user) use ($roleId, $areaId, &$selectedUsers) {
                $hasRole = $user->roles->contains(function($role) use ($roleId, $areaId) {
                    return $role->id == $roleId && $role->area_id == $areaId;
                });
                $notSelected = !$selectedUsers->contains('id', $user->id);
                return $hasRole && $notSelected;
            });

            // Ordenar: menos escalado primeiro, depois por prioridade da função
            $sortedCandidates = $candidates->sortBy(function($user) use ($userSchedules, $roleId) {
                $monthlyCount = isset($userSchedules[$user->id]) ? count($userSchedules[$user->id]) : 0;
                $rolePriority = $user->roles->firstWhere('id', $roleId)?->pivot->priority ?? 999;
                return [$monthlyCount, $rolePriority];
            });

            // Selecionar N usuários
            $selectedForRole = $sortedCandidates->take($count);

            // Adicionar informação de qual função cada usuário foi selecionado
            $selectedForRole->each(function($user) use ($roleId, $areaId) {
                $user->selected_for_role_id = $roleId;
                $user->selected_for_area_id = $areaId;
            });

            $selectedUsers = $selectedUsers->merge($selectedForRole);
        }
        return $selectedUsers;
    }

    /**
     * Atribui usuários à escala
     */
    private function assignUsersToSchedule(Schedule $schedule, Collection $users, array $roleRequirements): void
    {
        // Criar registros em user_schedule usando as informações de função já anexadas aos usuários
        $userScheduleData = $users->map(function($user) use ($schedule) {
            $data = [
                'schedule_id' => $schedule->id,
                'user_id' => $user->id,
                'status' => UserScheduleStatus::CONFIRMED->value,
            ];

            // Usar as informações de função anexadas durante a seleção
            if (isset($user->selected_for_role_id) && isset($user->selected_for_area_id)) {
                $data['role_id'] = $user->selected_for_role_id;
                $data['area_id'] = $user->selected_for_area_id;
            } else {
                // Fallback: buscar primeira função do usuário na primeira área
                $firstRole = $user->roles->first();
                if ($firstRole) {
                    $data['role_id'] = $firstRole->id;
                    $data['area_id'] = $firstRole->area_id;
                }
            }

            return $data;
        })->toArray();

        UserSchedule::insert($userScheduleData);
    }

    /**
     * Atualiza status da escala
     */
    private function setScheduleStatus(Schedule $schedule, ScheduleStatus $status): void
    {
        $schedule->update(['status' => $status]);
    }
}
