<?php

namespace App\Repositories;

use App\Enums\UserScheduleStatus;
use App\Models\UserSchedule;
use App\Models\Schedule;
use App\Models\User;
use App\Services\Interfaces\IStorageService;
use Illuminate\Database\Eloquent\Collection;

class UserScheduleRepository
{
    protected $model;
    protected IStorageService $storageService;

    public function __construct(UserSchedule $userSchedule, IStorageService $storageService)
    {
        $this->model = $userSchedule;
        $this->storageService = $storageService;
    }

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function getAvailableUsers(): Collection
    {
        $users = User::with(['areas.area', 'roles'])
            ->has('areas')
            ->get();

        $users->each(function ($user) {
            $user->setAttribute('areas', $user->areas->map(function ($userArea) {
                return ['id' => $userArea->area->id, 'name' => $userArea->area->name];
            }));
            
            $user->setAttribute('roles', $user->roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'area_id' => $role->area_id,
                ];
            }));
        });

        return $users;
    }

    public function getAllSchedules(): Collection
    {
        $user = auth()->user();
        $churchId = $user->church_id;

        $schedules = Schedule::with('userSchedules')
            ->join('users', 'schedule.user_creator', '=', 'users.id')
            ->where('schedule.approved', true) // Apenas escalas aprovadas
            ->where('users.church_id', $churchId) // Filtrar por church_id do usuário autenticado
            ->select('schedule.*')
            ->orderBy('schedule.created_at', 'desc') // Ordenar por data de criação (mais recentes primeiro)
            ->get();

        $schedules->each(function ($schedule) {
            $userSchedule = $schedule->userSchedules->where('user_id', auth()->id())->first();
            // Adiciona o status do usuário na escala (userStatus) e o status da escala (status)
            $schedule->setAttribute('userStatus', $userSchedule ? $userSchedule->status : null);
            // O status da escala vem do próprio Schedule
            $schedule->setAttribute('status', $schedule->status);

            // minhaEscala é true quando há registro do usuário em UserSchedule, não precisa ter status confirmed
            $schedule->setAttribute('minhaEscala', $schedule->userSchedules->contains(
                fn($userSchedule) =>
                $userSchedule->user_id === auth()->id()
            ));
        });

        return $schedules;
    }

    public function getMySchedules(): Collection
    {
        $userId = auth()->id();

        $schedules = Schedule::with('userSchedules')
            ->whereHas('userSchedules', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->where('approved', true) // Apenas escalas aprovadas
            ->orderBy('created_at', 'desc') // Ordenar por data de criação (mais recentes primeiro)
            ->get();

        $schedules->each(function ($schedule) use ($userId) {
            $userSchedule = $schedule->userSchedules->where('user_id', $userId)->first();
            // Adiciona o status do usuário na escala (userStatus) e o status da escala (status)
            $schedule->setAttribute('userStatus', $userSchedule ? $userSchedule->status : null);
            // O status da escala vem do próprio Schedule
            $schedule->setAttribute('status', $schedule->status);

            // minhaEscala é sempre true para este endpoint
            $schedule->setAttribute('minhaEscala', true);
        });

        return $schedules;
    }

    public function getById(int $id): UserSchedule
    {
        return $this->model->findOrFail($id);
    }

    public function getScheduleByScheduleId(int $id): Schedule
    {
        $schedule = Schedule::with('userSchedules')->findOrFail($id);

        $userSchedule = $schedule->userSchedules->where('user_id', auth()->id())->first();
        // Adiciona o status do usuário na escala (userStatus) e o status da escala (status)
        $schedule->setAttribute('userStatus', $userSchedule ? $userSchedule->status : null);
        // O status da escala vem do próprio Schedule
        $schedule->setAttribute('status', $schedule->status);

        // minhaEscala é true quando há registro do usuário em UserSchedule, não precisa ter status confirmed
        $schedule->setAttribute('minhaEscala', $schedule->userSchedules->contains(
            fn($userSchedule) =>
            $userSchedule->user_id === auth()->id()
        ));

        return $schedule;
    }

    public function getUsersByScheduleId(int $id): Collection
    {
        $schedule = Schedule::with(['userSchedules.user.areas', 'userSchedules.role'])->findOrFail($id);

        $users = $schedule->userSchedules->map(function ($userSchedule) {
            $user = $userSchedule->user;

            // Adiciona o campo 'area'
            $user->setAttribute('area', $userSchedule->area->name ?? null);

            // Adiciona o campo 'role'
            $user->setAttribute('role', $userSchedule->role ? $userSchedule->role->name : null);

            // Adiciona o campo 'statusSchedule'
            $user->setAttribute('statusSchedule', $userSchedule->status ?? null);

            // Generate signed URL for photo if exists
            if ($user->photo_path) {
                try {
                    $user->setAttribute('photo_url', $this->storageService->getSignedUrl($user->photo_path, 60));
                } catch (\Exception $e) {
                    \Log::warning('Failed to generate signed URL for user photo', [
                        'user_id' => $user->id,
                        'photo_path' => $user->photo_path,
                        'error' => $e->getMessage()
                    ]);
                    $user->setAttribute('photo_url', null);
                }
            } else {
                $user->setAttribute('photo_url', null);
            }

            return $user;
        });

        return $users;
    }

    public function create(array $data): UserSchedule
    {
        if (array_key_exists('status', $data) === false) {
            $data['status'] = UserScheduleStatus::CONFIRMED;
        }

        return $this->model->create($data);
    }

    public function update(array $data)
    {
        $userSchedule = $this->model->where('schedule_id', $data['schedule_id'])
            ->where('user_id', $data['user_id'])
            ->first();

        // $this->delete($userSchedule->id);

        // Cria uma nova entrada se não existir
        if ($userSchedule === null) {
            $userSchedule = $this->model->create($data);
            return $userSchedule;
        }

        $userSchedule->update($data);
        return $userSchedule;
    }

    public function deleteUserFromSchedule(array $data): bool
    {
        $userSchedule = UserSchedule::where('user_id', $data['user_id'])
            ->where('schedule_id', $data['schedule_id'])
            ->first();

        return $userSchedule->delete();
    }

    public function delete(int $id): bool
    {
        $userSchedule = $this->model->findOrFail($id);
        return $userSchedule->delete();
    }
}
