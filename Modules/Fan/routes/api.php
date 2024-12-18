<?php

use Illuminate\Support\Facades\Route;
use Modules\Fan\Http\Controllers\FanController;
use Modules\Fan\Http\Controllers\ReviewsController;
use Modules\Fan\Http\Controllers\SubscriptionController;




Route::middleware(['jwt_auth:fan'])->group(function(){
    Route::group(['prefix' => 'fan'],function(){
    Route::post('/reviews',[FanController::class,'create']);
    Route::get('/reviews',[FanController::class,'find']);
    Route::get('/escort-review/{id}',[FanController::class,'find_escort_reviews']);
    Route::post('/change-username',[FanController::class,'changeUsername']);
    Route::post('/add-view-count',[FanController::class,'addViewCount']);
    Route::post('/profile-views/{id}',[fanController::class,'profileViews']);

});
});
Route::get('/subscriptions',[SubscriptionController::class,'getSubscriptions']);
Route::get('/topLocation',[SubscriptionController::class,'topLocation']);
Route::get('/locations',[SubscriptionController::class,'locations']);
Route::post('/like-profile',[FanController::class,'likeProfile']);
Route::post('/slug-to-location',[SubscriptionController::class,'slugToLocation']);
Route::get('/list-reviews/{id?}',[SubscriptionController::class,'listReviews']);
Route::get('/all-blog-list',[FanController::class,'allBlogList']);
Route::get('all-list-reviews',[SubscriptionController::class,'getAllListReviews']);
Route::get('advert-list',[SubscriptionController::class,'getAdvertLists']);
Route::get('all-user-list',[SubscriptionController::class,'getAllUserList']);

