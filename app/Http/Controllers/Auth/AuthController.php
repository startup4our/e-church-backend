<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Log;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (! $token = Auth::attempt($credentials)) {
            Log::warning("User tried to login, but its credencials are invalid");
            return response()->json(['message' => 'Credenciais inválidas'], 401);
        }

        $user = Auth::user();

        switch ($user->status) {
            case UserStatus::INACTIVE:
                Log::warning('User tried to login, but it is inactive');
                return response()->json(['message' => 'Conta inativa. Contate o administrador.'], 401);

            case UserStatus::WAITING_APPROVAL:
                Log::warning("User tried to login, but it is waiting for admin approval of church [{$user->church_id}]");
                return response()->json(['message' => 'Conta aguardando aprovação.'], 401);

            case UserStatus::ACTIVE:
                Log::info("User [{$user->id}] has made login");
                return response()->json([
                    'user' => $user,
                    'access_token' => $token,
                    'token_type' => 'bearer'
                ]);
        }
    }

    public function register(Request $request){
        Log::info('Request to register user');
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'birthday' => 'required|date',
            'church_id' => 'required|exists:church,id',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'birthday' => $data['birthday'],
            'church_id' => $data['church_id'],
            'status' => UserStatus::WAITING_APPROVAL, // sempre WA
        ]);

        $token = Auth::login($user);

        return $this->respondWithToken($token);
    }


    public function logout()
    {
        Auth::logout();
        return response()->json(['message' => 'Logout feito com sucesso']);
    }

    public function refresh()
    {
        return $this->respondWithToken(Auth::refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ]);
    }
}
