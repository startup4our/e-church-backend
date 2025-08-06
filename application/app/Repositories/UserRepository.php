<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository {

    public function getAll(): array
    {
        return User::all()->toArray();
    }
}