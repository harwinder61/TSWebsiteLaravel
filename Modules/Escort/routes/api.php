<?php

use Illuminate\Support\Facades\Route;
use Modules\Escort\Http\Controllers\EscortController;
use Modules\Escort\Http\Controllers\ReviewsController;
use Modules\Escort\Http\Controllers\MasterController;
use Modules\Escort\Http\Controllers\MastersController;
use Modules\Escort\Http\Controllers\MediaController;
use Modules\Escort\Http\Controllers\OrderController;


Route::get('/test',[EscortController::class,'test']);

Route::group(['prefix' => 'escort'],function(){

    Route::get('/profile',[EscortController::class,'find']);
    Route::put('/profile',[EscortController::class,'update']);
    Route::get('/reviews',[ReviewsController::class,'list']);
    Route::post('/media/gallary',[MediaController::class,'addGallary']);
    Route::post('/media/promovideo',[MediaController::class,'addPromoVideo']);
    Route::get('/media/promovideo',[MediaController::class,'getPromoVideo']);
    Route::post('/orders',[OrderController::class,'createOrder']);
    Route::post('/webhook/payment-status-update',[OrderController::class,'webhook_payment_status_update']);
    Route::get('/subscriptions',[OrderController::class,'getSubscription']);
});

Route::get('/locations/countries',[MasterController::class,'countries']);
Route::get('/locations/regions',[MasterController::class,'regions']);
Route::get('/locations/cities',[MasterController::class,'cities']);
Route::get('/locations/nationality',[MasterController::class,'nationality']);
Route::get('/master-data',[MastersController::class,'getMasterData']);



Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('escort', EscortController::class)->names('escort');
});
