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
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->sentence(),
            'local' => $this->faker->optional()->address(),
            'date_time' => $this->faker->dateTimeBetween('now', '+1 year'),
            'observation' => $this->faker->optional()->sentence(),
            'type' => $this->faker->randomElement(ScheduleType::values()),
            'approved' => $this->faker->boolean(),
            'user_creator' => User::factory(),
        ];
    }
}
