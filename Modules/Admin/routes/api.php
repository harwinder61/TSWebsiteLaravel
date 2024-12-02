<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\AdminController;
use Modules\Admin\Http\Controllers\FanController;
use Modules\Admin\Http\Controllers\EscortController;

Route::middleware(['jwt_auth:admin'])->group(function(){
    Route::group(['prefix'=>'admin'],function(){
        Route::post('/plan/{plan_code}',[AdminController::class,'updatePlan']);
        Route::get('/plan/{id}',[AdminController::class,'getPlan']);
        Route::put('/update-profile/{id}',[AdminController::class,'updateProfile']);
        Route::get('/fans',[FanController::class,'getFans']);
        Route::get('/escorts',[EscortController::class,'getEscorts']);
        Route::get('/users',[AdminController::class,'getUsers']);
        Route::get('/profile/{id}',[AdminController::class,'getProfile']);
        Route::get('/inquiries',[AdminController::class,'inquiryFormList']);
        Route::get('/recent-signups',[AdminController::class,'recentSignups']);
        Route::get('/permissions',[AdminController::class,'getPermissions']);
        Route::post('/assign-permissions/{id}',[AdminController::class,'assignPermissions']);
        Route::post('/create-subscription',[AdminController::class,'createSubscription']);
        Route::get('/user-quick-list',[AdminController::class,'userQuickList']);
        Route::post('/update-plan-details/{plan_code}',[AdminController::class,'updatePlanDetails']);
        Route::get('/recent-inquiries',[AdminController::class,'inquiriesList']);
        Route::get('/recent-purchases',[AdminController::class,'recentPurchases']);
        Route::get('/spotlight-media',[AdminController::class,'spotlightMedia']);
        Route::post('/blog',[AdminController::class,'blog']);
        Route::get('/live-adverts-users',[AdminController::class,'getLiveAdvertsUsers']);
        Route::get('/admin-users',[AdminController::class,'getAdminUsers']);
    });
});


