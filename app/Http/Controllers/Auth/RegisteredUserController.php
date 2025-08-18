<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'birthday' => ['required', 'date'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => strtolower($request->email), 
            'password' => Hash::make($request->password),
            'birthday' => $request->birthday,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return response()->json($user, 201);
    }
}
