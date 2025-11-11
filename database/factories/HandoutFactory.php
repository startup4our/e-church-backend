<?php

namespace Database\Factories;

use App\Models\Handout;
use App\Models\Church;
use App\Models\Area;
use App\Enums\HandoutStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class HandoutFactory extends Factory
{
    protected $model = Handout::class;

    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('now', '+1 month');
        $endDate = (clone $startDate)->modify('+7 days');

        return [
            'church_id' => Church::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'priority' => $this->faker->randomElement(['high', 'normal']),
            'status' => $this->faker->randomElement([
                HandoutStatus::ACTIVE->value,
                HandoutStatus::PENDING->value,
                HandoutStatus::INACTIVE->value,
            ]),
            'area_id' => null,
            'link_name' => $this->faker->optional()->words(2, true),
            'link_url' => $this->faker->optional()->url(),
            'image_url' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => HandoutStatus::ACTIVE->value,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => HandoutStatus::PENDING->value,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => HandoutStatus::INACTIVE->value,
        ]);
    }
}

