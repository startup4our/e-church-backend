<?php

namespace Database\Factories;

use App\Models\DateException;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DateExceptionFactory extends Factory
{
    protected $model = DateException::class;

    public function definition()
    {
        return [
            'exception_date' => $this->faker->date(),
            'shift' => $this->faker->randomElement(['morning', 'afternoon', 'night']),
            'justification' => $this->faker->sentence,
            'user_id' => User::factory(),
        ];
    }
}
