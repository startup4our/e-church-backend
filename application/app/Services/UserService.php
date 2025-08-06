<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use App\Services\Interfaces\IUserService;


class UserService implements IUserService{

    // Injeta o repositório de usuários para realizar operações de banco
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Register the user in db
     * @param \App\Models\User $user
     * @return bool
     */
    public function registerNewUser(User $user) : bool
    {
        return $user->save();
    }
    
}