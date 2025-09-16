<?php

namespace Database\Factories;

use App\Enums\UserScheduleStatus;
use App\Models\Area;
use App\Models\Schedule;
use App\Models\User;
use App\Models\UserSchedule;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserScheduleFactory extends Factory
{
    protected $model = UserSchedule::class;

    public function definition(): array
    {
        // não preciso criar novos usuários ou áreas, apenas referenciar os que já existem de forma aleatoria e dinamica
        $user = User::inRandomOrder()->first();
        $schedule = Schedule::inRandomOrder()->first();
        $area = Area::inRandomOrder()->first();

        return [
            'user_id' => $user->id,
            'schedule_id' => $schedule->id,
            'area_id' => $area->id,
            'status' => $this->faker->randomElement(UserScheduleStatus::values()),
        ];
    }
}
