<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\app\Http\Controllers\AuthController;

use Modules\Auth\app\Http\Middleware\AuthMiddleware;
Route::post('login', [AuthController::class, 'login']);
Route::post('login-with-gmail', [AuthController::class, 'loginWithGmail']);
Route::post('register', [AuthController::class, 'register']);
Route::post('register-with-gmail', [AuthController::class, 'registerWithGmail']);
Route::get('logout',[AuthController::class,'logout']); 
Route::get('user',[AuthController::class,'me']);
Route::get('verify-email/{token}',[AuthController::class,'verifyEmail']);
Route::post('recover-password',[AuthController::class,'recoverPassword']);
Route::post('reset-password',[AuthController::class,'resetPassword']);
Route::get('verification-token',[AuthController::class,'verificationToken']);
Route::post('verify-email-token',[AuthController::class,'verificationEmailToken']);
Route::post('change-password',[AuthController::class,'changePassword']);
Route::post('reset-old-email',[AuthController::class,'resetOldEmail']);
Route::post('login-with-gmail', [AuthController::class, 'loginWithGmail']);
Route::post('register-with-gmail', [AuthController::class, 'registerWithGmail']);








 









