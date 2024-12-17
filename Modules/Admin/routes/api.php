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
        Route::get('/admin-users/{user_type?}',[AdminController::class,'getAdminUsers']);
        Route::get('/user-permissions/{id}',[AdminController::class,'getUserPermissions']);
        Route::post('/approve-review/{id}',[AdminController::class,'approveReview']);
        Route::post('/disapprove-review/{id}',[AdminController::class,'disapproveReview']);
        Route::post('/delete-review/{id}',[AdminController::class,'deleteReview']);
        Route::post('/edit-blog/{id}',[AdminController::class,'editBlog']);
        Route::post('/delete-blog/{id}',[AdminController::class,'deleteBlog']);
        Route::get('/all-advert-users',[AdminController::class,'getAllAdvertUsers']);
        Route::post('/user',[AdminController::class,'newUser']);
        Route::put('/user/{id}',[AdminController::class,'userProfile']);
        Route::post('/create-forum',[AdminController::class,'createForum']);
        Route::get('/get-varifiacation-list',[AdminController::class,'getVarifiacationList']);
        Route::post('/post-comment',[AdminController::class,'postComment']);  
        Route::get('/get-forum-post/{id}',[AdminController::class,'getForumPost']);
        Route::get('/get-comments',[AdminController::class,'getComments']);
        Route::post('/verified-status/{escort_id}',[AdminController::class,'verifiedStatus']);
        Route::get('/escort-varification-list',[AdminController::class,'escortVarificationList']);
        Route::get('/fan-varification-list',[AdminController::class,'fanVarificationList']);
        Route::get('/reminder-category',[AdminController::class,'reminderCategory']);
        Route::post('/create-reminder',[AdminController::class,'createReminder']);
        Route::get('/get-reminder',[AdminController::class,'getReminder']);
        Route::post('/post-reminder-comment',[AdminController::class,'postReminderComment']);
        Route::get('/get-reminder-comment',[AdminController::class,'getReminderComment']);
        Route::post('/verified-status-form',[AdminController::class,'verifiedStatusForm']);
        Route::post('/post-email-template',[AdminController::class,'postEmailTemplate']);
        Route::get('/get-email-template',[AdminController::class,'getEmailTemplate']);
        Route::post('/add-comment/{id}',[AdminController::class,'addComment']);
        Route::post('/remove-comment/{id}',[AdminController::class,'removeComment']);
        Route::get('/get-forum-list/{slug}',[AdminController::class,'getForumSlugList']);
        Route::get('/get-forum-comments/{id}',[AdminController::class,'getForumComments']);
        Route::post('/aproove-comment/{id}',[AdminController::class,'aprooveComment']);
        Route::post('/reject-comment/{id}',[AdminController::class,'rejectComment']);
        Route::post('/aproove-forum/{id}',[AdminController::class,'aprooveForum']);
        Route::post('/reject-forum/{id}',[AdminController::class,'rejectForum']); 

        
     



    });
    

});

Route::get('/blog/{id?}',[AdminController::class,'getBlog']);
Route::get('/blog-slug/{slug?}',[AdminController::class,'getBlogBySlug']);
Route::get('/get-forum/{id}',[AdminController::class,'getForum']);




