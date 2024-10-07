<?php

use Illuminate\Support\Facades\Route;
use Modules\Escort\Http\Controllers\EscortController;

/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 *
 * Here is where you can register API routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * is assigned the "api" middleware group. Enjoy building your API!
 *
*/
Route::get('/profile',[EscortController::class,'getProfile']);
Route::put('/profile',[EscortController::class,'updateProfile']);
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('escort', EscortController::class)->names('escort');
});
