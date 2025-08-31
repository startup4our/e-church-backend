<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\User;
use App\Models\Chat;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition()
    {
        return [
            'content' => $this->faker->sentence,
            'image_path' => null,
            'sent_at' => $this->faker->dateTime,
            'chat_id' => Chat::factory(),
            'user_id' => User::factory(),
        ];
    }
}
