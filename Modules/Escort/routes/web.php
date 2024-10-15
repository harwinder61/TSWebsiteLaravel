<?php

use Illuminate\Support\Facades\Route;
use Modules\Escort\Http\Controllers\EscortController;
use Modules\Escort\Http\Controllers\OrderController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/payment-success',[OrderController::class,'paymentSuccess']);
Route::get('/payment-cancel',[OrderController::class,'paymentCancel']);
Route::group([], function () {
    Route::resource('escort', EscortController::class)->names('escort');
});
