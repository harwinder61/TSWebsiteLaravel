<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\app\Http\Controllers\AuthController;

use Modules\Auth\app\Http\Middleware\AuthMiddleware;

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


/*Route::group(['middleware' => 'jwt.auth'], function () {
    Route::get('/login', function () {
        return auth()->user();
    });
});
*/



Route::post('login', [AuthController::class, 'login']);//->middleware(AuthMiddleware::class);
Route::post('register', [AuthController::class, 'register']);
Route::get('logout',[AuthController::class,'logout']); 
Route::get('user',[AuthController::class,'me']);
Route::get('verify-email/{token}',[AuthController::class,'verifyEmail']);
Route::post('recover-password',[AuthController::class,'recoverPassword']);
Route::post('reset-password',[AuthController::class,'resetPassword']);


