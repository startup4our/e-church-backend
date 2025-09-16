<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\User;
use App\Models\UserArea;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserAreaFactory extends Factory
{
    protected $model = UserArea::class;

    public function definition(): array
    {
        // não preciso criar novos usuários ou áreas, apenas referenciar os que já existem de forma aleatoria e dinamica
        $area = Area::inRandomOrder()->first();
        $user = User::inRandomOrder()->first();

        return [
            'area_id' => $area->id,
            'user_id' => $user->id
        ];
    }
}
