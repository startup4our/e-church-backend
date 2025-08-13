<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UnavailabilityController;

Route::apiResource('unavailability', UnavailabilityController::class);
