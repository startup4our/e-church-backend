<?php

namespace App\Services\Interfaces;

use App\Models\User;

interface IUserService {

    public function registerNewUser(User $user) : bool;


}