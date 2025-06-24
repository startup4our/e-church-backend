<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Church>
 */
class ChurchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
{
    return [
        'name' => $this->faker->company . ' Church',
        'cep' => $this->faker->postcode,
        'street' => $this->faker->streetName,
        'number' => (string) $this->faker->buildingNumber,
        'complement' => $this->faker->optional()->secondaryAddress,
        'quarter' => $this->faker->word, // pode ser neighborhood ou district, depende do país
        'city' => $this->faker->city,
        'state' => $this->faker->stateAbbr,
    ];
}

}
