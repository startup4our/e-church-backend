<?php


use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

Route::prefix('v1/auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [UserController::class, 'register']);

});

Route::get('/teste', fn () => response()->json(['ok' => true]));

