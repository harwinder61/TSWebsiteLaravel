<?php

use Illuminate\Support\Facades\Route;
use Modules\Fan\Http\Controllers\FanController;
use Modules\Fan\Http\Controllers\ReviewsController;



Route::group(['prefix' => 'fan'],function(){

    Route::post('/reviews',[FanController::class,'create']);
});

