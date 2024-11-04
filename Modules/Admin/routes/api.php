<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\AdminController;
use Modules\Admin\Http\Controllers\FanController;
use Modules\Admin\Http\Controllers\EscortController;
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


    });
});