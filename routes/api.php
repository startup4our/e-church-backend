<?php

use App\Http\Controllers\AreaController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DateExceptionController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UnavailabilityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChurchController;
use App\Http\Controllers\SongController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\RecordingController;
use App\Http\Controllers\ScheduleController;

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

Route::middleware(['auth:api'])->get('/v1/user', function (Request $request) {
    return $request->user();
});

//private routes
Route::prefix('v1')->middleware(['auth:api'])->group(function () {
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
    Route::apiResource('message', MessageController::class);
    Route::apiResource('date-exception', DateExceptionController::class);
    Route::apiResource('permission', PermissionController::class);
    Route::apiResource('links', LinkController::class);
    Route::apiResource('recordings', RecordingController::class);
    Route::apiResource('schedules', ScheduleController::class);

    Route::post('schedules/{schedule}/generate', [ScheduleController::class, 'generate']);
});



Route::prefix('v1/auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('refresh', [AuthController::class, 'refresh'])->middleware('auth:api');

    // esqueceu senha
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store']);
    Route::post('reset-password', [NewPasswordController::class, 'store']);

    // verify email
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('auth:api')
        ->name('verification.send');
});
