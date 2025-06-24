<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Permission>
 */
class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'create_scale' => $this->faker->boolean(30),
            'read_scale' => $this->faker->boolean(80),
            'update_scale' => $this->faker->boolean(50),
            'delete_scale' => $this->faker->boolean(20),
            'create_music' => $this->faker->boolean(30),
            'read_music' => $this->faker->boolean(80),
            'update_music' => $this->faker->boolean(50),
            'delete_music' => $this->faker->boolean(20),
            'manage_users' => true,
            'manage_church_settings' => true,
            'manage_app_settings' => true,
        ];
    }
}
