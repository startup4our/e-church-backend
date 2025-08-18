<?php

use App\Http\Controllers\AreaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
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
});