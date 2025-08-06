<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Interfaces\IUserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

abstract class UserController
{
    protected $service;

    public function __construct (
        IUserService $service
    ) {
        $this->service = $service;
    }

    public function register(Request $request){
        // Validação dos dados de entrada
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'birthday' => 'required|date',
            'password' => 'required|string|min:6',
        ]);

        // Cria uma nova instância do modelo User com os dados validados
        $user = new User($validated);

        // Chama o método register do serviço de usuário para registrar o usuário
        $res = $this->service->registerNewUser($user);

        // Verifica se o registro foi bem-sucedido
        if (!$res) {
            return response('Error registering user', Response::HTTP_BAD_REQUEST);
        }

        return response('User registered successfully', Response::HTTP_CREATED);
    
    }

}
