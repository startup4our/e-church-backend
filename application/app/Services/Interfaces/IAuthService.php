<?php

namespace App\Services\Interfaces;

use App\Models\User;

interface IAuthService {

    public function login(array $credentials) : false|array;

}