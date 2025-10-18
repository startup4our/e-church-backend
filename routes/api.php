<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\ChurchController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DateExceptionController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RecordingController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SongController;
use App\Http\Controllers\UnavailabilityController;
use App\Http\Controllers\UserScheduleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StorageController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| These routes are loaded by the RouteServiceProvider within the "api" middleware group.
|
*/

Route::middleware(['auth:api'])->get('/v1/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Rotas Privadas (com autenticação)
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->middleware(['auth:api'])->group(function () {

    // User schedules
    Route::get('user-schedules/show-all-schedules', [UserScheduleController::class, 'getAllSchedules']);
    Route::get('user-schedules/show-users-by-schedule-id/{scheduleId}', [UserScheduleController::class, 'getUsersByScheduleId']);
    Route::get('user-schedules/show-schedule-by-schedule-id/{scheduleId}', [UserScheduleController::class, 'getScheduleByScheduleId']);
    Route::get('user-schedules/show-schedule-by-schedule/create', [UserScheduleController::class, 'store']);
    Route::get('user-schedules/show-available-users', [UserScheduleController::class, 'getAvailableUsers']);
    Route::post('user-schedules/add-user-in-schedule', [UserScheduleController::class, 'addUserInSchedule']);
    Route::delete('user-schedules/remove-user-in-schedule', [UserScheduleController::class, 'removeUserInSchedule']);
    Route::delete('user-schedules/remove-user-from-schedule', [UserScheduleController::class, 'removeUserFromSchedule']);
    Route::patch('user-schedules/update-status', [UserScheduleController::class, 'updateStatus']);

    // CRUD endpoints
    Route::apiResource('areas', AreaController::class);
    Route::get('areas/{id}/users', [AreaController::class, 'getUsers']);
    Route::put('areas/{areaId}/users/{userId}/switch', [AreaController::class, 'switchUserArea']);
    Route::apiResource('unavailability', UnavailabilityController::class);
    Route::apiResource('churches', ChurchController::class);
    Route::apiResource('songs', SongController::class);
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('chats', ChatController::class);
    Route::apiResource('message', MessageController::class);
    Route::apiResource('date-exception', DateExceptionController::class);
    Route::apiResource('permission', PermissionController::class);
    Route::apiResource('links', LinkController::class);
    Route::apiResource('recordings', RecordingController::class);
    // Route::apiResource('schedules', ScheduleController::class);

    // Geração automática de escala
    Route::post('schedules/{schedule}/generate', [ScheduleController::class, 'generate']);

    // Convite (apenas envio — requer autenticação)
    Route::post('invites', [InviteController::class, 'store']);

    // User profile routes
    Route::get('users/profile', [UserController::class, 'profile']);
    Route::put('users/profile', [UserController::class, 'updateProfile']);

    // User management routes
    Route::get('users/by-church', [UserController::class, 'getUsersByChurch']);
    Route::put('users/{id}', [UserController::class, 'updateUser']);
    Route::patch('users/{id}/toggle-status', [UserController::class, 'toggleUserStatus']);

    // Permission routes
    Route::put('permission/user/{userId}', [PermissionController::class, 'updateByUserId']);

    // Storage routes
    Route::post('storage/change-user-photo', [StorageController::class, 'changeUserPhoto']);

    // Chats de usuário
    Route::post('chats/user/', [ChatController::class, 'getChats']);
});

/*
|--------------------------------------------------------------------------
| Rotas Públicas
|--------------------------------------------------------------------------
*/

// Convite — rota acessada pelo link de e-mail (sem autenticação)
Route::get('v1/invites/{token}', [InviteController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Autenticação
|--------------------------------------------------------------------------
*/
Route::prefix('v1/auth')->group(function () {

    // Registro e autenticação
    Route::post('register-church', [AuthController::class, 'registerChurch']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:api');

    // Recuperação de senha
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store']);
    Route::post('reset-password', [NewPasswordController::class, 'store']);

    // Verificação de e-mail
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('auth:api')
        ->name('verification.send');
});
