<?php

namespace Database\Factories;

use App\Enums\ScheduleType;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduleFactory extends Factory
{
    protected $model = Schedule::class;

    public function definition(): array
    {
        $user = User::inRandomOrder()->first();
        $startDate = $this->faker->dateTimeBetween('now', '+1 year');
        $endDate = (clone $startDate)->modify('+3 hour');

        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->sentence(),
            'local' => $this->faker->address(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'observation' => $this->faker->optional()->sentence(),
            'type' => $this->faker->randomElement(ScheduleType::values()),
            'approved' => $this->faker->boolean(),
            'user_creator' => $user->id,
        ];
    }
}
