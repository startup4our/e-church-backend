<?php

namespace Database\Factories;

use App\Models\Song;
use Illuminate\Database\Eloquent\Factories\Factory;

class SongFactory extends Factory
{
    protected $model = Song::class;

    public function definition(): array
    {
        return [
            'cover_path'  => $this->faker->imageUrl(300, 300, 'music', true),
            'name'        => $this->faker->sentence(3),
            'artist'      => $this->faker->name(),
            'spotify_id'  => $this->faker->optional()->bothify('??########'),
            'preview_url' => $this->faker->optional()->url(),
            'duration'    => $this->faker->numberBetween(60, 600),
            'album'       => $this->faker->optional()->sentence(2),
            'spotify_url' => $this->faker->optional()->url(),
        ];
    }
}
