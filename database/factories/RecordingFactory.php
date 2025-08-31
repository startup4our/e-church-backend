<?php

namespace Database\Factories;

use App\Enums\RecordingType;
use App\Models\Recording;
use App\Models\Song;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecordingFactory extends Factory
{
    protected $model = Recording::class;

    public function definition(): array
    {
        return [
            'path' => $this->faker->url(),
            'type' => $this->faker->randomElement(RecordingType::values()),
            'description' => $this->faker->optional()->sentence(),
            'song_id' => Song::factory(),
        ];
    }
}
