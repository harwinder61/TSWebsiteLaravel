<?php

use Illuminate\Support\Facades\Route;
use Modules\Escort\Http\Controllers\EscortController;
use Modules\Escort\Http\Controllers\ReviewsController;
use Modules\Escort\Http\Controllers\MasterController;



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
Route::group(['prefix' => 'escort'],function(){

    Route::get('/profile',[EscortController::class,'getProfile']);
    Route::put('/profile',[EscortController::class,'updateProfile']);
    Route::get('/reviews',[ReviewsController::class,'getUsers']);

});

Route::get('/locations/countries',[MasterController::class,'countries']);
Route::get('/locations/regions',[MasterController::class,'regions']);
Route::get('/locations/cities',[MasterController::class,'cities']);
Route::get('/locations/nationality',[MasterController::class,'nationality']);



Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('escort', EscortController::class)->names('escort');
});
