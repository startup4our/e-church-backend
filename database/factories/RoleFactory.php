<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\Area;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'name'        => $this->faker->unique()->jobTitle(),
            'description' => $this->faker->optional()->sentence(),
            'area_id'     => Area::factory(),
        ];
    }
}
