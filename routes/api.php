<?php

use App\Http\Controllers\AreaController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\UnavailabilityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChurchController;
use App\Http\Controllers\SongController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->get('/v1/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    // Create RESTful endpoints to area
    // GET /api/v1/areas
    // GET /api/v1/areas/{id}
    // POST /api/v1/areas
    // PUT /api/v1/areas/{id}
    // DELETE /api/v1/areas/{id}
    Route::apiResource('areas', AreaController::class);
    Route::apiResource('unavailability', UnavailabilityController::class);
    Route::apiResource('churches', ChurchController::class);
    Route::apiResource('songs', SongController::class);
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('chats', ChatController::class);
});

Route::prefix('v1/auth')->group(function () {

    Route::post('/register', [RegisteredUserController::class, 'store'])
                    ->middleware('guest')
                    ->name('register');

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
                    ->middleware('guest')
                    ->name('login');

    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
                    ->middleware('guest')
                    ->name('password.email');

    Route::post('/reset-password', [NewPasswordController::class, 'store'])
                    ->middleware(['guest', 'throttle:6,1'])
                    ->name('password.store');

    Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
                    ->middleware(['auth', 'signed', 'throttle:6,1'])
                    ->name('verification.verify');

    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                    ->middleware(['auth', 'throttle:6,1'])
                    ->name('verification.send');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
                    ->middleware('auth')
                    ->name('logout');
});