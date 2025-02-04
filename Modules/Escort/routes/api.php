<?php

use Illuminate\Support\Facades\Route;
use Modules\Escort\Http\Controllers\EscortController;
use Modules\Escort\Http\Controllers\ReviewsController;
use Modules\Escort\Http\Controllers\MasterController;
use Modules\Escort\Http\Controllers\MastersController;
use Modules\Escort\Http\Controllers\MediaController;
use Modules\Escort\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Mail;
use Modules\Admin\app\Models\User;
use Modules\Escort\Notifications\ReviewSubmitted;

Route::middleware(['jwt_auth'])->group(function(){
Route::group(['prefix' => 'escort'],function(){
    Route::get('/profile',[EscortController::class,'find']);
    Route::put('/profile',[EscortController::class,'update']);  
    Route::post('/media/gallery',[MediaController::class,'addGallery']);
    Route::get('/media/gallery',[MediaController::class,'getGallery']);
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
    Route::post('/hide-profile',[EscortController::class,'hideProfile']);   
     Route::put('/orders',[OrderController::class,'updateOrder']);
    // Route::post('/delete-profile',[EscortController::class,'deleteProfile']);
    Route::put('/orders',[OrderController::class,'updateOrder']);
    Route::get('/active-subscription',[EscortController::class,'getActiveSubscription']);
    Route::post('/verify',[EscortController::class,'verify']);
    Route::get('/get-verify',[EscortController::class,'getVerify']);
    Route::get('/featured-ts-girl',[EscortController::class,'featuredTsGirl']);
    Route::get('/previous-subscriptions',[OrderController::class,'getEscortPreviousSubscriptions']);
    Route::get('/latest-subscription',[OrderController::class,'getLatestEscortSubscription']);
    Route::patch('/update-latest-subscription',[OrderController::class,'updateLatestEscortSubscription']);
    Route::post('/add-extra-locations',[OrderController::class,'extraLocationsUpdatedOrder']);
    Route::get('reviws-escort-fanlist',[ReviewsController::class,'getEscortFanlist']);


});
});
Route::post('/profile-views/{id}',[EscortController::class,'profileViews']);
Route::get('/locations/countries',[MasterController::class,'countries']);
Route::get('/locations/regions',[MasterController::class,'regions']);
Route::get('/locations/cities',[MasterController::class,'cities']);
Route::get('/locations/nationality',[MasterController::class,'nationality']);
Route::get('/master-data',[MastersController::class,'getMasterData']);
Route::get('/plans',[MasterController::class,'plans']);
Route::post('/inquiry-form',[EscortController::class,'inquiryForm']);
Route::get('/escort-profile-id/{id}',[EscortController::class,'getEscortProfile']);
Route::get('/get-all-media',[MediaController::class,'getAllMedia']);
Route::get('/search/header',[OrderController::class,'getLocationAndSubscriptions']);
Route::post('/location-id-to-name',[OrderController::class,'locationIdsToLocationNames']);
// Route::get('/test-email-review', function () {
//     Mail::raw('This is a test email.', function ($message) {
//         $message->to('adminTs0011@yopmail.com')
//                 ->subject('Test Email');
//     });
// });

