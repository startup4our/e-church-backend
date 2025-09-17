<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Area;
use App\Models\PermissionTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class PermissionTemplateFactory extends Factory
{
    protected $model = PermissionTemplate::class;

    public function definition(): array
    {
        return [
            'name'        => $this->faker->word().' template',
            'description' => $this->faker->sentence(),
            'created_by'  => User::factory(),
            'area_id'     => Area::factory(),
            'read_scale'  => true,
            'read_music'  => true,
        ];
    }
}
