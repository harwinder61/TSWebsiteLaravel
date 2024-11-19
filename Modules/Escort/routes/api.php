<?php

use Illuminate\Support\Facades\Route;
use Modules\Escort\Http\Controllers\EscortController;
use Modules\Escort\Http\Controllers\ReviewsController;
use Modules\Escort\Http\Controllers\MasterController;
use Modules\Escort\Http\Controllers\MastersController;
use Modules\Escort\Http\Controllers\MediaController;
use Modules\Escort\Http\Controllers\OrderController;



Route::middleware(['jwt_auth'])->group(function(){
Route::group(['prefix' => 'escort'],function(){

    Route::get('/profile',[EscortController::class,'find']);
    Route::put('/profile',[EscortController::class,'update']);  
    Route::get('/reviews',[ReviewsController::class,'list']);
    Route::post('/media/gallary',[MediaController::class,'addGallary']);
    Route::get('/media/gallary',[MediaController::class,'getGallary']);
    Route::post('/media/promovideo',[MediaController::class,'addPromoVideo']);
    Route::get('/media/promovideo',[MediaController::class,'getPromoVideo']);
    Route::post('/orders',[OrderController::class,'createOrder']);
    Route::post('/webhook/payment-status-update',[OrderController::class,'webhook_payment_status_update']);
    Route::get('/subscriptions',[OrderController::class,'getSubscription']);
    Route::get('/media',[MediaController::class,'getMedia']);
    Route::post('/media/single',[MediaController::class,'mediaSingle']);
    Route::post('/update-media',[EscortController::class,'updateMedia']);
    Route::post('/update-subscription',[EscortController::class,'updateSubscription']);
    Route::post('/delete-profile',[EscortController::class,'deleteProfile']);
    Route::post('/hide-profile',[EscortController::class,'hideProfile']);    Route::put('/orders',[OrderController::class,'updateOrder']);
    Route::post('/delete-profile',[EscortController::class,'deleteProfile']);
    Route::post('/profile-views/{id}',[EscortController::class,'profileViews']);

});
});

Route::get('/locations/countries',[MasterController::class,'countries']);
Route::get('/locations/regions',[MasterController::class,'regions']);
Route::get('/locations/cities',[MasterController::class,'cities']);
Route::get('/locations/nationality',[MasterController::class,'nationality']);
Route::get('/master-data',[MastersController::class,'getMasterData']);
Route::get('/plans',[MasterController::class,'plans']);
Route::post('/inquiry-form',[EscortController::class,'inquiryForm']);
Route::get('/escort-profile-id/{id}',[EscortController::class,'getEscortProfile']);
Route::get('/get-all-media/{escort_id}',[MediaController::class,'getAllMedia']);
Route::get('/search/header',[OrderController::class,'getLocationAndSubscriptions']);



