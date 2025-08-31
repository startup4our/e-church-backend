<?php

namespace Database\Factories;

use App\Models\Link;
use App\Models\Song;
use Illuminate\Database\Eloquent\Factories\Factory;

class LinkFactory extends Factory
{
    protected $model = Link::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'destination' => $this->faker->url(),
            'description' => $this->faker->sentence(),
            'song_id' => Song::factory(),
        ];
    }
}
