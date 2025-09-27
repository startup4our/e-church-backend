<?php

namespace App\Repositories;

use App\Enums\UserScheduleStatus;
use App\Models\UserSchedule;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserScheduleRepository
{
    protected $model;

    public function __construct(UserSchedule $userSchedule)
    {
        $this->model = $userSchedule;
    }

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function getAvailableUsers(): Collection
    {
        $users = User::with('areas.area')->whereNot('id', auth()->id())
            ->has('areas')
            ->get();

        $users->each(function ($user) {
            $user->setAttribute('areas', $user->areas->map(function ($userArea) {
                return ['id' => $userArea->area->id, 'name' => $userArea->area->name];
            }));
        });

        return $users;
    }

    public function getAllSchedules(): Collection
    {
        $schedules = Schedule::with('userSchedules')
            ->where('approved', true) // Apenas escalas aprovadas
            ->get();

        $schedules->each(function ($schedule) {
            $userSchedule = $schedule->userSchedules->where('user_id', auth()->id())->first();
            // Adiciona o status e a informação se é a escala do usuário autenticado
            $schedule->setAttribute('status', $userSchedule ? $userSchedule->status : null);

            // minhaEscala é true quando há registro do usuário em UserSchedule, não precisa ter status confirmed
            $schedule->setAttribute('minhaEscala', $schedule->userSchedules->contains(
                fn($userSchedule) =>
                $userSchedule->user_id === auth()->id()
            ));
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
        // Adiciona o status e a informação se é a escala do usuário autenticado
        $schedule->setAttribute('status', $userSchedule ? $userSchedule->status : null);

        // minhaEscala é true quando há registro do usuário em UserSchedule, não precisa ter status confirmed
        $schedule->setAttribute('minhaEscala', $schedule->userSchedules->contains(
            fn($userSchedule) =>
            $userSchedule->user_id === auth()->id()
        ));

        return $schedule;
    }

    public function getUsersByScheduleId(int $id): Collection
    {
        $schedule = Schedule::with(['userSchedules.user.areas'])->findOrFail($id);

        $users = $schedule->userSchedules->map(function ($userSchedule) {
            $user = $userSchedule->user;

            // Adiciona o campo 'area'
            $user->setAttribute('area', $userSchedule->area->name);

            // Adiciona o campo 'statusSchedule'
            $user->setAttribute('statusSchedule', $userSchedule->status ?? null);
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
