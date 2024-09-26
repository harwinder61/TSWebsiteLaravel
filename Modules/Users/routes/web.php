<?php

use Illuminate\Support\Facades\Route;
use Modules\Users\app\Http\Controllers\UserController;

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

Route::group([], function () {
    //Route::resource('users', UsersController::class)->names('users');
    Route::get('/users',[UserController::class,'index']);
    Route::post('/users',[UserController::class,'store']);
});
