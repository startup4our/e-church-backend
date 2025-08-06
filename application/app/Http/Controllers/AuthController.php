<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\IAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

abstract class AuthController
{
    protected $authService;

    public function __construct (
        IAuthService $service
    ) {
        $this->authService = $service;
    }

    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        
        if ($validator->fails()) {
            return response('Invalid Body', Response::HTTP_BAD_REQUEST);
        }
        
        $credentials = $request->only('email', 'password');

        $res = $this->authService->login($credentials);

        if ($res['success']) {
            return response()->json([
                'access_token' => $res['access_token'],
                'token_type' => 'bearer',
                'userId' => $res['user']['id'],
                'userEmail' => $res['user']['email'],
                'photoPath' => $res['user']['photo_path'],
                'username' => $res['user']['name'],
                'expires_in' =>  $res['expires_in']
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'error' => $res['error'],
            ], Response::HTTP_UNAUTHORIZED);
        }

    }

}
