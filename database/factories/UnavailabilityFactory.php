<?php

namespace Database\Factories;

use App\Models\Unavailability;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnavailabilityFactory extends Factory
{
    protected $model = Unavailability::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'weekday' => $this->faker->numberBetween(0, 6),
            'shift'   => $this->faker->randomElement(['manha', 'tarde', 'noite']),
        ];
    }
}
