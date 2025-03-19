<?php
namespace Modules\Escort\Http\Controllers;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Escort\app\Models\Profile;
use Modules\Escort\app\Models\ProfileRates;
use Illuminate\Support\Facades\Response;
use Modules\Auth\app\Http\Middleware\AuthMiddleware;
use Illuminate\Support\Facades\Validator;
use App\Models\Region;
use App\Models\Cities;
use App\Models\Countries;
use App\Models\Nationality;
use App\Services\Resp;
use Illuminate\Support\Facades\Log;
use Modules\Auth\app\Models\AuthUser;
use Modules\Escort\app\Http\Middleware\AuthEscort;
use Modules\Escort\app\Models\Orders;
use App\Models\Location;
use Modules\Escort\app\Models\Inquiry;
use App\Enums\InqueryFormSubject;
use App\Models\Media;
use Modules\Escort\app\Models\EscortSubscription;
use Modules\Escort\app\Models\Verify;
use Illuminate\Support\Facades\File;
use App\Models\Plan;
use App\Models\BaseSubscription;
use App\Models\BaseReviews;
use App\Mail\EmailHelper;
use App\Models\User;
use Illuminate\Support\Str;
use App\Mail\SmsHelper;
use Illuminate\Support\Facades\Mail;
use Modules\Admin\app\Models\SmsTemplates;
use Modules\Admin\app\Models\SmsLogs;
use Illuminate\Support\Facades\Crypt;

use Illuminate\Support\Facades\Http;
class EscortController extends Controller
{
    public function __construct()
    {
        $this->middleware(AuthMiddleware::class)->except(['profileViews','inquiryForm','newVerifyEmail','veriffSession','veriffDocumentUpload','veriffDocumentCompleted','veriffStatus','VeriffDecision','getVeriffStatus','veriffWebhook','veriffEventWebhook','veriffFullAutoWebhook']);
    } 
 

    // public function updateSubscription(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'subscription_id' => 'required|exists:subscriptions,id',
    //         'image_id' => 'required|exists:media,id'
    //     ]);
    
    //     if ($validator->fails()) {
    //         return Resp::fieldErrors(['field_errors' => $validator->errors()]);
    //     }
        
    //     $user = auth()->user();
    //     $subscription = EscortSubscription::with('profile')->find($request->subscription_id); // Join with profile
    
    //     if (!$subscription) {
    //         return Resp::error(['message' => 'Subscription not found'], 404);
    //     }
    
    //     $subscription->update([
    //         'image_id' => $request->image_id
    //     ]);
    
    //     // Update additional fields
    //     if ($request->input('start_date')) {
    //         $subscription->start_date = $request->input('start_date');
    //         $subscription->save();
    //     }
    //     if ($request->input('end_date')) {
    //         $subscription->end_date = $request->input('end_date');
    //         $subscription->save();
    //     }
    //     if ($request->input('plan_code')) {
    //         $subscription->plan_code = $request->input('plan_code');
    //         $subscription->save();
    //     }
    
    //     // Prepare dynamic data for the SMS
    //     $dynamicData = [
    //         '[SUBSCRIPTION_ID]' => $subscription->id,
    //         '[USER_NAME]' => $user->firstname . ' ' . $user->lastname,
    //         '[LOGIN_URL]' => env('LOGIN_URL'),
    //     ];
    
    //     // Send SMS using the phone number from the joined profile
    //     $phoneNumber = $subscription->profile->phone_number; // Get phone number from profile
    //     if ($request->input('type') == 'whatsapp') {
    //         $smsResponse = SmsHelper::dynamicsendSms(
    //             'whatapp_admin_new_user_added',
    //             $dynamicData,
    //             $phoneNumber,
    //             $user,
    //             true
    //         );
    //     } else if ($request->input('type') == 'sms') {
    //         $smsResponse = SmsHelper::dynamicsendSms(
    //             'admin_new_user_added',
    //             $dynamicData,
    //             $phoneNumber,
    //             $user,
    //             false
    //         );
    //     }
    
    //     return Resp::success([
    //         'message' => 'Subscription updated successfully and SMS sent',
    //         'subscription' => $subscription
    //     ]);
    // }

    // public function updateSubscription(Request $request)
    // {
    //     // Validate the incoming request
    //     $validator = Validator::make($request->all(), [
    //         'subscription_id' => 'required|exists:subscriptions,id',
    //         'image_id' => 'required|exists:media,id'
    //     ]);
    
    //     if ($validator->fails()) {
    //         return Resp::fieldErrors(['field_errors' => $validator->errors()]);
    //     }
    
    //     $user = auth()->user();
    //     $subscription = EscortSubscription::with('profile')->find($request->subscription_id);
    
    //     if (!$subscription) {
    //         return Resp::error(['message' => 'Subscription not found'], 404);
    //     }
    
    //     // Update the subscription with the new image ID
    //     $subscription->image_id = $request->image_id;
    
    //     // Update additional fields if provided
    //     if ($request->input('start_date')) {
    //         $subscription->start_date = $request->input('start_date');
    //     }
    //     if ($request->input('end_date')) {
    //         $subscription->end_date = $request->input('end_date');
    //     }
    //     if ($request->input('plan_code')) {
    //         $subscription->plan_code = $request->input('plan_code');
    //     }
    
    //     // Save all changes at once
    //     $subscription->save();
    
    //     // Decrypt the user's password
    //     $decryptedPassword = null;
    //     if ($subscription->escort->secret) {
    //         $decryptedPassword = Crypt::decryptString($subscription->escort->secret);
    //     }
    
    //     // Prepare dynamic data for the SMS
    //     $dynamicData = [
    //         '[USER_LOGIN]' => $subscription->escort->username,
    //         '[LOGIN_URL]' => env('LOGIN_URL'),
    //         '[USER_PASSWORD]' => $decryptedPassword,
    //     ];
    
    //     // Construct the SMS message dynamically using placeholders
    //     $smsMessage = "Hi {$dynamicData['[USER_LOGIN]']},\n" .
    //                   "We have created an account for you on Transbunnies.com. Claim your free advert here - {$dynamicData['[LOGIN_URL]']}\n" .
    //                   "Username: {$dynamicData['[USER_LOGIN]']}\n" .
    //                   "Password: {$dynamicData['[USER_PASSWORD]']}\n" .
    //                   "Regards,\n" .
    //                   "Team Transbunnies\n" .
    //                   "THE PLACE TO BE IN!";
    
    //     // Send SMS using the phone number from the joined profile
    //     $phoneNumber = $subscription->profile->phone_number;
    
    //     // Debugging: Log the input type
    //     Log::info('SMS Type: ' . $request->input('type'));
    
    //     $smsResponse = null;
    //     if (in_array($request->input('type'), ['whatsapp', 'sms'])) {
    //         $template = $request->input('type') == 'whatsapp' ? 'whatapp_admin_new_user_added' : 'admin_new_user_added';
    //         $smsResponse = SmsHelper::dynamicsendSms($template, $dynamicData, $phoneNumber, $user, $request->input('type') == 'whatsapp');
    //     } else {
    //         Log::error('Invalid SMS type: ' . $request->input('type'));
    //         return Resp::error(['message' => 'Invalid SMS type'], 400);
    //     }
    
    //     // Log the SMS message
    //     SmsLogs::create([
    //         'message' => $smsMessage, // Save the constructed SMS message
    //         'to' => $phoneNumber,
    //         'status' => 1, // Assuming success, adjust based on actual response
    //         'user_id' => $user->id,
    //         'from' => env('TWILIO_PHONE_NUMBER'),
    //     ]);
    
    //     return Resp::success([
    //         'message' => 'Subscription updated successfully and SMS sent',
    //         'subscription' => $subscription
    //     ]);
    // }

    public function updateSubscription(Request $request)
    {
        // Validate the incoming request
        $validator = Validator::make($request->all(), [
            'subscription_id' => 'required|exists:subscriptions,id',
            'image_id' => 'required|exists:media,id'
        ]);
    
        if ($validator->fails()) {
            return Resp::fieldErrors(['field_errors' => $validator->errors()]);
        }
    
        $user = auth()->user();
        $subscription = EscortSubscription::with('profile')->find($request->subscription_id);
    
        if (!$subscription) {
            return Resp::error(['message' => 'Subscription not found'], 404);
        }
    
        // Update the subscription with the new image ID
        $subscription->image_id = $request->image_id;
    
        // Update additional fields if provided
        if ($request->input('start_date')) {
            $subscription->start_date = $request->input('start_date');
        }
        if ($request->input('end_date')) {
            $subscription->end_date = $request->input('end_date');
        }
        if ($request->input('plan_code')) {
            $subscription->plan_code = $request->input('plan_code');
        }
    
        // Save all changes at once
        $subscription->save();
    
        // Decrypt the user's password
        $decryptedPassword = null;
        if ($subscription->escort->secret) {
            $decryptedPassword = Crypt::decryptString($subscription->escort->secret);
        }
    
           // Prepare dynamic data for the SMS template
           $dynamicData = [
            '[USER_LOGIN]' => $subscription->escort->username,
            '[LOGIN_URL]' => env('LOGIN_URL'),
            '[USER_PASSWORD]' => $decryptedPassword,
        ];

        // print_r($dynamicData);
        if ($request->input('status') ? 1 : 0) {
            $smsResponse = SmsHelper::dynamicsendSms(
                'admin_new_user_added',
                $dynamicData,
                $user->profile->phone_number,
                $user,
                true
            );
        }  else if (($request->input('status') ? 1 : 0) == 0) {
            $smsResponse = SmsHelper::dynamicsendSms(
                'admin_new_user_added',
                $dynamicData,
                $user->profile->phone_number,
                $user,
                false
            );
        }
        return Resp::success([
            'message' => 'Subscription updated successfully and SMS sent',
            'subscription' => $subscription
        ]);
    }
    public function newVerifyEmail(Request $request)
    {
        $user = auth()->user();
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
    
        if ($validator->fails()) {
            return Resp::fieldErrors(['field_errors' => $validator->errors()]);
        }
    
        // Log the token being verified
        // Log::info('Verifying email with token: ' . $token);
        
        // $user = AuthUser::where('verification_token')->first();

        
        if (!$user) {
            return Resp::error(['message' => 'Email verification failed. Invalid token.']);
        }
        // Update the user's email without checking for a match
        $user->email = $request->input('email');
        $user->save(); // Save the updated user
        $verification_token = $user->verification_token;

        if($verification_token == ""){
            $verification_token = Str::random(30);
            $user->verification_token = $verification_token;
            $user->save();
        }
    
        EmailHelper::sendDynamicEmail(
            'ts_email_verification',
            [
                '[USER_LOGIN]' => $user->username,
                '[USER_EMAIL]' => $user->email,
                '[VERIFIED_EMAIL_LINK]' => env('WEBAPP_URL') . "/account-verification?token=" . $verification_token
            ],
            $user->email
        );
    
        // Return success response
        return Resp::success(['message' => 'Email verified and updated successfully']);
    }


   


    // public function deleteProfile(Request $request)
// {
//     $validator = Validator::make($request->all(), [
//         'is_delete' => 'required|boolean'
//     ]);

//     if ($validator->fails()) {
//         return Resp::fieldErrors(['field_errors' => $validator->errors()]);
//     }

//     $user = auth()->user();
//     // Only update if is_delete is true
//     if ($request->is_delete) {
//         $user->delete_on = now()->subDays(30); // Set to 30 days ago for immediate eligibility
//         $user->is_delete = $request->is_delete; 
//         $user->save();

//         $profile = Profile::where('escort_id', $user->id)->first();
//         if ($profile) {
//             $profile->delete();
//         }

//         EmailHelper::sendDynamicEmail('ts_delete_profile', 
//             ['[USER_LOGIN]' => $user->username], 
//             $user->email);
        
//         return Resp::success(['user' => $user], 'Profile deleted successfully');
//     } else {
//         if ($user->is_delete) {
//             $user->is_delete = 0; // Restore the account
//             $user->delete_on = null; // Clear the delete_on date
//             $user->save();
        
//             return Resp::success(['user' => $user], 'Profile restored successfully');
//         } else {
//             // Only update if is_delete is true
//             if ($request->is_delete) {
//                 $user->delete_on = now()->subDays(30); // Set to 30 days ago for immediate eligibility
//                 $user->is_delete = 1; // Mark for deletion
//                 $user->save();

//         EmailHelper::sendDynamicEmail('ts_delete_profile', 
//             ['[USER_LOGIN]' => $user->username], 
//             $user->email);
        
//         return Resp::success(['user' => $user], 'Profile deleted successfully');
//     }
//     return Resp::error(['message' => 'Invalid request']);
// }
// }}

public function deleteProfile(Request $request)
{
    $validator = Validator::make($request->all(), [
        'is_delete' => 'required|boolean'
    ]);

    if ($validator->fails()) {
        return Resp::fieldErrors(['field_errors' => $validator->errors()]);
    }

    $user = auth()->user();
    if ($request->is_delete) {
        $user->delete_on = now(); // Set to 30 days ago for immediate eligibility
        $user->is_delete = 1;
        $user->save();

        // Delete associated profile
        $profile = Profile::where('escort_id', $user->id)->first();
        // if ($profile) {
        //     $profile->delete();
        // }

        // Send email notification
        EmailHelper::sendDynamicEmail('ts_delete_profile', 
            ['[USER_LOGIN]' => $user->username], 
            $user->email);
        
        return Resp::success(['user' => $user], 'Profile deleted successfully');
    } else {
        if ($user->is_delete) {
            $user->is_delete = 0; // Restore the account
            $user->delete_on = null; // Clear the delete_on date
            $user->save();
            return Resp::success(['user' => $user], 'Profile restored successfully');
        }
    }
    return Resp::error(['message' => 'Invalid request']);
}






    public function hideProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'is_hidden' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return Resp::fieldErrors(['field_errors' => $validator->errors()]);
        }
        $user = auth()->user();
        if ($request->is_hidden) {
            $user->is_hidden = $request->is_hidden;
            $user->save();
            EmailHelper::sendDynamicEmail('ts_hide_profile', 
            ['[USER_LOGIN]' => $user->username], 
            $user->email);
            return Resp::success(['message' => 'Profile hidden successfully']);
        }
        else{
            $user->is_hidden = $request->is_hidden;
            $user->save();
            EmailHelper::sendDynamicEmail('ts_show_profile', 
            ['[USER_LOGIN]' => $user->username], 
            $user->email);
            return Resp::success(['message' => 'Profile shown successfully']);
        }
    }



    public function featuredTsGirl(Request $request)
    {
        $user = auth()->user();
        $subscription = BaseSubscription::where('escort_id', $user->id)->latest()->first();
        return Resp::success(['has_subscription' => (bool) $subscription]);
    }
     
    public function getVerify(Request $request)
    {
        $user = auth()->user();
        $verify = Verify::where('escort_id', $user->id)->first();
        return Resp::success(['verify' => $verify]);
    }
    
    public function verify(Request $request)
    {
        try {
            $user = auth()->user();
            $verification_exists=Verify::where('escort_id',$user->id)->first();
            if($verification_exists){
                return Resp::error(['message'=>"User already exists in verification table!"]);
            }

            $profile = Profile::where('escort_id', $user->id)->first();
            if(!$profile){
                return Resp::error(['message' => 'Profile not found']);
            }
            $validator = Validator::make($request->all(), [
                'passport_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5000000',
                'selfie_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5000000',
            ]);
    
            if ($validator->fails()) {
                return Resp::fieldErrors(['field_errors' => $validator->errors()]);
            }
    
            if ((!$request->hasFile('passport_image') && !$request->hasFile('selfie_image'))) {
                // Update verified_status to 4 if no images are selected
                $profile = $user->profile;
                $profile->verified_status = 3;
                $verify = Verify::where('escort_id', $user->id)->first();
                if(!$verify){
                    $verify = new Verify();
                    $verify->escort_id = $user->id;
                }
                $verify->verified_status = 3;
                $verify->save();
                $profile->save();
    
                return Resp::success([
                    'message' => 'Verification status updated to 3 due to missing images',
                    'user_data' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                ]);
            }
    
            // Process Images
            $userId = $user->id;
            $userFolder = 'uploads/media/user_' . $userId;
    
            $directoryPath = public_path($userFolder);
    
            if (!File::isDirectory($directoryPath)) {
                File::makeDirectory($directoryPath, 0755, true);
            }
    
            // Save Passport Image
            $passportImage = $request->file('passport_image');
            $passportImageName = 'passport_' . time() . '_' . uniqid() . '.' . $passportImage->getClientOriginalExtension();
            $passportImage->move($directoryPath, $passportImageName);
    
            // Save Selfie Image
            $selfieImage = $request->file('selfie_image');
            $selfieImageName = 'selfie_' . time() . '_' . uniqid() . '.' . $selfieImage->getClientOriginalExtension();
            $selfieImage->move($directoryPath, $selfieImageName);
    
            // Save to Database
            $verify = new Verify();
            $verify->passport_image = $userFolder . '/' . $passportImageName;
            $verify->selfie_image = $userFolder . '/' . $selfieImageName;
            $verify->escort_id = $userId;
            $verify->verified_status = 2;
            $verify->save();
            $profile = $user->profile;
            $profile->verified_status = 2;
            $profile->save();
            // $verify->verified_status = 2;
            // $verify->save();
    
            return Resp::success([
                'message' => 'Verify details saved successfully',
                'passport_image_path' => $userFolder . '/' . $passportImageName,
                'selfie_image_path' => $userFolder . '/' . $selfieImageName,
                'user_data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Verification Error: ' . $e->getMessage());
            return Resp::error(['message' => 'An error occurred while processing your request','error'=>$e->getMessage()], 500);
        }
    }
    



    public function getActiveSubscription(Request $request)
    {
        $user = auth()->user();
        $subscriptions = EscortSubscription::where('escort_id', $user->id)
            ->where('status', 'active')
            ->get(); 
        $profile = Profile::where('escort_id', $user->id)->first();
        $media = Media::where('escort_id', $user->id)->get();
        
        return Resp::success([
            'subscriptions' => $subscriptions, 
        ]);
    }

    public function profileViews($id, Request $request)
    {
        $user = auth()->user();
        $profile = AuthUser::where('id', $id)
                           ->with('profile')
                           ->first();
    
        if (!$profile || !$profile->profile) {
            return Resp::error(['message' => 'User not found or profile not found']);
        }
    
        $profile->profile->increment('profile_views');
    
        return Resp::success(['message' => 'Profile views updated successfully']);
    }


 

//     public function updateSubscription(Request $request)
// {
//     $validator = Validator::make($request->all(), [
//         'subscription_id' => 'required|exists:subscriptions,id',
//         'image_id' => 'required|exists:media,id'
//     ]);

//     if ($validator->fails()) {
//         return Resp::fieldErrors(['field_errors' => $validator->errors()]);
//     }
//     $user = auth()->user();
//     $subscription = EscortSubscription::find($request->subscription_id);
    
//     if (!$subscription) {
//         return Resp::error(['message' => 'Subscription not found'], 404);
//     }
//     $subscription->update([
//         'image_id' => $request->image_id
//     ]);
//     if($request->input('start_date')){
//         $subscription->start_date = $request->input('start_date');
//         $subscription->save();
//     }
//     if($request->input('end_date')){
//         $subscription->end_date = $request->input('end_date');
//         $subscription->save();
//     }
//     if($request->input('plan_code')){
//         $subscription->plan_code = $request->input('plan_code');
//         $subscription->save();
//     }
//     return Resp::success([
//         'message' => 'Subscription updated successfully',
//         'subscription' => $subscription
        
//         ]);
//     }


// public function updateMedia(Request $request)
// {
//     $validator = Validator::make($request->all(), [
//         'gallery' => 'array',                   
//         'gallery.*' => 'exists:media,id',      
//         'private_gallery' => 'array',            
//         'private_gallery.*' => 'exists:media,id', 
//         'promo_video' => 'exists:media,id',                                                                                                             
//         'description' => 'nullable|string',
//     ]);

//     // Return validation errors if any
//     if ($validator->fails()) {
//         return Resp::fieldErrors(['field_errors' => $validator->errors()]);
//     }

//     // $promo_type = null;
//     // if ($promo_video = " ") {
//     //     $promo_type = 'promo_video';
//     // }



//     $user = auth()->user();
//     $profile = Profile::where('escort_id', $user->id)->first(); // Initialize profile here

//     if ($request->has('gallery')) {
//         $galleryIds = collect($request->input('gallery'))->flatten()->toArray();
        
//         Media::where('escort_id', $user->id)
//             ->where('type', 'gallery')
//             ->whereIn('id', $galleryIds)
//             ->update(['is_temp' => false]);

//         Media::where('escort_id', $user->id)
//             ->where('type', 'gallery')
//             ->whereNotIn('id', $galleryIds)
//             ->forceDelete();
//     }

//     if ($request->has('private_gallery')) {
//         $privateGalleryIds = collect($request->input('private_gallery'))->flatten()->toArray();
        
//         Media::where('escort_id', $user->id)
//             ->where('type', 'private_gallery')
//             ->whereIn('id', $privateGalleryIds)
//             ->update(['is_temp' => false]);

//         Media::where('escort_id', $user->id)
//             ->where('type', 'private_gallery')
//             ->whereNotIn('id', $privateGalleryIds)
//             ->forceDelete();
//     }

//     if ($request->has('promo_video') && $request->input('promo_video') !== null) {
//         $promoVideoId = $request->input('promo_video');
        
//         Media::where('escort_id', $user->id)
//             ->where('type', 'promo_video')
//             ->where('id', $promoVideoId)
//             ->update(['is_temp' => false]);

//         Media::where('escort_id', $user->id)
//             ->where('type', 'promo_video')
//             ->where('id', '!=', $promoVideoId)
//             ->forceDelete();
//     }

//     if ($request->has('description')) {
//         if ($profile) {
//             $profile->description = $request->input('description');
//             $profile->save();
//         }
//     }

//     if ($request->has('gallery') && $request->has('private_gallery') && $request->has('promo_video') && $request->has('description')) {
//         if ($profile) {
//             $profile->is_media = 1;
//             $profile->save();
//         }
//     }

//     return Resp::success(['message' => 'Media updated successfully', 'profile' => $profile]);
// }

public function updateMedia(Request $request)
{
    $validator = Validator::make($request->all(), [
        'gallery' => 'array',                   
        'gallery.*' => 'exists:media,id',      
        'private_gallery' => 'array',            
        'private_gallery.*' => 'exists:media,id', 
        'promo_video' => 'nullable|exists:media,id', // Updated to allow null
        'description' => 'nullable|string',
    ]);

    // Return validation errors if any
    if ($validator->fails()) {
        return Resp::fieldErrors(['field_errors' => $validator->errors()]);
    }

    $user = auth()->user();
    $profile = Profile::where('escort_id', $user->id)->first(); // Initialize profile here

    if ($request->has('gallery')) {
        $galleryIds = collect($request->input('gallery'))->flatten()->toArray();
        
        Media::where('escort_id', $user->id)
            ->where('type', 'gallery')
            ->whereIn('id', $galleryIds)
            ->update(['is_temp' => false]);

        Media::where('escort_id', $user->id)
            ->where('type', 'gallery')
            ->whereNotIn('id', $galleryIds)
            ->forceDelete();
    }

    if ($request->has('private_gallery')) {
        $privateGalleryIds = collect($request->input('private_gallery'))->flatten()->toArray();
        
        Media::where('escort_id', $user->id)
            ->where('type', 'private_gallery')
            ->whereIn('id', $privateGalleryIds)
            ->update(['is_temp' => false]);

        Media::where('escort_id', $user->id)
            ->where('type', 'private_gallery')
            ->whereNotIn('id', $privateGalleryIds)
            ->forceDelete();
    }

    if ($request->has('promo_video') && $request->input('promo_video') !== null) {
        $promoVideoId = $request->input('promo_video');
        
        Media::where('escort_id', $user->id)
            ->where('type', 'promo_video')
            ->where('id', $promoVideoId)
            ->update(['is_temp' => false]);

        Media::where('escort_id', $user->id)
            ->where('type', 'promo_video')
            ->where('id', '!=', $promoVideoId)
            ->forceDelete();
    }

    if ($request->has('description')) {
        if ($profile) {
            $profile->description = $request->input('description');
            $profile->save();
        }
    }

    if ($request->has('gallery') && $request->has('private_gallery') && $request->has('promo_video') && $request->has('description')) {
        if ($profile) {
            $profile->is_media = 1;
            $profile->save();
        }
    }

    return Resp::success(['message' => 'Media updated successfully', 'profile' => $profile]);
}


    public function getEscortProfile($id,Request $request)
    {
        $user = auth()->user();
        $profile = Profile::where('escort_id', $user->id)->first();
        $media = Media::where('escort_id', $user->id)->get();
        if ($profile) {
            $profile = Profile::where('escort_id', $user->id)->first();
            $profile->rates;
            return Resp::success([
                'id' => $user->id,
                'profile' => $profile,
                'media' => $media,
                'rates' => $profile->rates
            ]);
        }
        return Resp::error(['message' => 'No active subscription found'], 404);
    }

    public function inquiryForm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            return Resp::fieldErrors(['field_errors' => $validator->errors()]);
        }
        $inquiryForm = new Inquiry();
        $inquiryForm->subject = $request->input('subject');
        $inquiryForm->name = $request->input('name');
        $inquiryForm->email = $request->input('email');
        $inquiryForm->message = $request->input('message');
        $inquiryForm->save();

        return Resp::success(['message' => 'Inquiry form submitted successfully']);
    }

    public function find(Request $request)
    {
        $user = auth()->user();
        $profile_data = $user->profile;
        $profile_data->rates;
        
        

        // $profile_data = Profile::find($user->id);
        

        if (!$profile_data) {
            
            return Resp::error(['message' => 'No profile found'], 404);
        }
        return Resp::success(['list' => $profile_data]);
    }



    public function update(Profile $profile, Request $request)
    {

        $user = auth()->user();
        $userType = $user->user_type;
        $user->is_delete = 0; // Restore the account
        $user->delete_on = null; // Clear the delete_on date
        $user->save();

        if ($userType == 1) {   
            return Resp::error(['Unauthorized user is not an escort']);
        } elseif ($userType == 2) {

            $validator = Validator::make($request->all(), $profile->rules());

            if ($validator->fails()) {
                return Resp::fieldErrors(['field_errors' => $validator->errors()]);
            }

            $user_id = $user->id;
            $profile = AuthUser::find($user_id)->profile;

            if (!$profile) {
                return Response::json(['error' => 'Profile not found'], 404);
            }


            $city_id = $request->input('city_id');
            $city_exists = Location::where('id', $city_id)->where('type', 'city')->first();

            $county_id = $city_exists->parent_id;
            $county_exists = Location::where('id', $county_id)->where('type', 'county')->first();
            if (!$county_exists) {
                return Resp::error(['County not found']);
            }
            $region_id = $county_exists->parent_id;
            $region_exists = Location::where('id', $region_id)->where('type', 'region')->first();
            if (!$region_exists) {
                return Resp::error(['Region not found']);
            }


            $languages = $request->input('languages');
            $updated = $profile->update([
                'name' => $request->input('name'),
                'phone_number' => $request->input('phone_number'),
                'gender' => $request->input('gender'),
                'date_of_birth' => $request->input('date_of_birth'),
                'orientation' => $request->input('orientation'),
                'ethnicity' => $request->input('ethnicity'),
                'nationality' => $request->input('nationality'),
                'height' => $request->input('height'),
                'weight' => $request->input('weight'),
                'hair' => $request->input('hair'),
                'eyes' => $request->input('eyes'),
                'breasts_size' => $request->input('breasts_size'),
                'breasts_cup' => $request->input('breasts_cup'),
                'butt' => $request->input('butt'),
                'body' => $request->input('body'),
                'cock_size' => $request->input('cock_size'),
                'languages' => $request->input('languages'),
                'offer_services_to' => $request->input('offer_services_to'),
                'has_twitter' => $request->input('has_twitter'),
                'has_snapchat' => $request->input('has_snapchat'),
                'has_instagram' => $request->input('has_instagram'),
                'has_tiktok' => $request->input('has_tiktok'),
                'twitter_handle' => $request->input('twitter_handle'),
                'snapchat_handle' => $request->input('snapchat_handle'),
                'instagram_handle' => $request->input('instagram_handle'),
                'tiktok_handle' => $request->input('tiktok_handle'),
                'extra_services' => $request->input('extra_services'),
                'location' => $request->input('location'),
                'city_id' => $request->input('city_id'),
                'region_id' => $region_id,
                'county_id' => $county_id,
                'is_profile' => true,
                'description' => $request->input('description'),
                'is_incall_enabled' => $request->input('is_incall_enabled'),
                'is_outcall_enabled' => $request->input('is_outcall_enabled'),
                'has_onlyfans' => $request->input('has_onlyfans'),
                'has_manyvids' => $request->input('has_manyvids'),
                'has_fancentro' => $request->input('has_fancentro'),
                'onlyfans_handle' => $request->input('onlyfans_handle'),
                'manyvids_handle' => $request->input('manyvids_handle'),
                'fancentro_handle' => $request->input('fancentro_handle'),
                'country_code' => $request->input('country_code'),
                'age' => $request->input('age'),
            ]);
            if (!$updated) {
                return Resp::error(['error' => 'Failed to update profile'], 500);
            }

            $profile_data = Profile::where('escort_id', $user->id)->first();
            Log::info($profile_data);
            if(!$profile_data){
                return Resp::error(['Profile not found !']);
            }
            $is_incall_enabled = $request->input('is_incall_enabled');
            $is_outcall_enabled = $request->input('is_outcall_enabled');
            $baseRules = [
                'rates' => 'required|array',

            ];
            $customMessages = [];
            $rateFields = ['15_min', '30_min', '1_hour', '2_hour', '4_hour', 'overnight'];

            if ($is_incall_enabled) {

                $baseRules["rates.*.category"] = [
                    'required',
                    'in:Incall,Outcall',
                ];
                foreach ($rateFields as $field) {
                    $baseRules["rates.*.{$field}"] = [
                        'required',
                    ];
                    $customMessages["rates.*.{$field}.required"] = "The {$field} field is required for Incall rates.";
                }
            }

            if ($is_outcall_enabled) {
                $baseRules["rates.*.category"] = [
                    'required',
                    'in:Outcall,Incall',
                ];
                foreach ($rateFields as $field) {
                    $baseRules["rates.*.{$field}"] = [
                        'required',
                    ];
                    $customMessages["rates.*.{$field}.required"] = "The {$field} field is required for Outcall rates.";
                }
            }

            $validator = Validator::make($request->all(), $baseRules, $customMessages);
            if ($validator->fails()) {
                return Resp::fieldErrors(['field_errors' => $validator->errors()]);
            }

            $profile_rates = ProfileRates::where('escort_id', $profile_data->escort_id)->get();
            $rates_data = $request->input('rates');
            if (!$profile_rates) {
                $profile_rates = ProfileRates::create([
                    'escort_id' => $profile_data->escort_id,
                ]);
            }
            foreach ($rates_data as $rate) {
                $category = strtolower($rate['category']);
                $profile_rates = ProfileRates::where('escort_id', $profile_data->escort_id)
                    ->where('category', $category)
                    ->first();

                if (($category == 'outcall' && $is_outcall_enabled) || ($category == 'incall' && $is_incall_enabled)) {
                    $rate_data = [
                        'category' => $rate['category'],
                        '15_min' => $rate['15_min'],
                        '30_min' => $rate['30_min'],
                        '1_hour' => $rate['1_hour'],
                        '2_hour' => $rate['2_hour'],
                        '4_hour' => $rate['4_hour'],
                        'overnight' => $rate['overnight'],
                    ];

                    if ($profile_rates) {
                        $profile_rates->update($rate_data);
                    } else {
                        $rate_data['escort_id'] = $profile_data->escort_id;
                        ProfileRates::create($rate_data);
                    }
                }

                //when outcall or incall is disabled and there is exisiting data then update it to 0
                else if (($category == 'outcall' && !$is_outcall_enabled) || ($category == 'incall' && !$is_incall_enabled)) {
                    $rate_data = [
                        'category' => $rate['category'],
                        '15_min' => 0,
                        '30_min' => 0,
                        '1_hour' => 0,
                        '2_hour' => 0,
                        '4_hour' => 0,
                        'overnight' => 0,
                    ];

                    if ($profile_rates) {
                        $profile_rates->update($rate_data);
                    }
                }
            }
            $profile_data = Profile::where('escort_id', $profile_data->escort_id)->first();
            $profile_data->rates;
            return Resp::success(['details' => $profile_data]);
        } else {
            return Resp::error(['Invalid user type']);
        }
        return Resp::error(['No user type found']);



    }

    public function veriffSession(Request $request){
        $data = [
            'verification' => [
                'callback' => 'https://veriff.com',
                'person' => [
                    'firstName' => $request->input('verification.person.firstName'),
                    'lastName' => $request->input('verification.person.lastName'),
                    'idNumber' => $request->input('verification.person.idNumber')
                ],
                'document' => [
                    'number' => $request->input('verification.document.number'),
                    'type' => $request->input('verification.document.type'),
                    'country' => $request->input('verification.document.country')
                ],
                'address' => [
                    'fullAddress' => $request->input('verification.address.fullAddress')
                ],
                'vendorData' => $request->input('verification.vendorData'),
                'endUserId' => $request->input('verification.endUserId'),
                'consents' => $request->input('verification.consents')
            ]
        ];
        
        try {
            $baseUrl=config('services.veriff.base_url');
            $response = Http::withHeaders([
                'X-AUTH-CLIENT' => config('services.veriff.key'),
                'Content-Type' => 'application/json'
            ])->post($baseUrl.'/v1/sessions', $data);

            // If Veriff responds with an error, run fallback logic
            if ($response->failed()) {

                return response()->json(['message' => 'Something went wrong.','data'=>$response->json()],400);
                Log::error('Veriff API call failed: ' . $response->json());
                //return $this->fallbackLogic($request);
            }

            $veriffSession = $response->json();
            return response()->json([
                'success' => true,
                'session' => $veriffSession
            ]);
        } catch (\Exception $e) {
            Log::error('Exception while calling Veriff: ' . $e->getMessage());
            // If an exception occurs, run your fallback logic
            // return $this->fallbackLogic($request);
            return Resp::json(['message' => 'Something went wrong.'.$e->getMessage()]);
        }
    }


    public function veriffDocumentUpload(Request $request,$id){
        $data = [
            'image' => [
                'context' => $request->input('image.context'),
                'content' => $request->input('image.content')
            ]
        ];
        try {
            $baseUrl=config('services.veriff.base_url');
            $jsonBody=json_encode($data);
            // Generate HMAC signature using the secret key
            $signature = hash_hmac(
                'sha256',
                $jsonBody,
                config('services.veriff.secret')
            );

            $response = Http::withHeaders([
                'X-AUTH-CLIENT' => config('services.veriff.key'),
                'Content-Type' => 'application/json',
                'X-HMAC-SIGNATURE'=>$signature
            ])->post($baseUrl.'/v1/sessions/'.$id.'/media', $data);

            // If Veriff responds with an error, run fallback logic
            if ($response->failed()) {
                //return Resp::error(['message' => 'Something went wrong.'.$response->json()]);
                return response()->json(['message' => 'Something went wrong.','data'=>$response->json()],400);
                //return $this->fallbackLogic($request);
            }

            $veriffSession = $response->json();
            return response()->json([
                'success' => true,
                'session' => $veriffSession
            ]);
        } catch (\Exception $e) {
            Log::error('Exception while calling Veriff: ' . $e->getMessage());
            // If an exception occurs, run your fallback logic
            // return $this->fallbackLogic($request);
            return Resp::json(['message' => 'Something went wrong.'.$e->getMessage()]);
        }
    }

    public function veriffDocumentCompleted(Request $request, $id){
        // Construct the data object from the request
        // Simplify the data structure to only include required fields
        $data = [
            'document' => [
                'number' => $request->input('data.document.number'),
                'type' => $request->input('data.document.type'),
                'country' => $request->input('data.document.country')
            ],
            'person' => [
                'firstName' => $request->input('data.person.firstName'),
                'lastName' => $request->input('data.person.lastName'),
                'dateOfBirth' => $request->input('data.person.dateOfBirth'),
                'gender' => $request->input('data.person.gender')
            ]
        ];
        $veriffData = $request->input('data', []);

        try {
            $baseUrl = config('services.veriff.base_url');
            $jsonBody = json_encode($veriffData);
            
           
            // Generate HMAC signature using the secret key
            $signature = hash_hmac(
                'sha256',
                $jsonBody,
                config('services.veriff.secret')
            );

            $response = Http::withHeaders([
                'X-AUTH-CLIENT' => config('services.veriff.key'),
                'Content-Type' => 'application/json',
                'X-HMAC-SIGNATURE' => $signature
            ])->post($baseUrl.'/v1/sessions/'.$id.'/collected-data', $veriffData);

            if ($response->failed()) {
                
                
                // return Resp::error([
                //     'success' => false,
                //     'message' => 'Data submission failed: '.$response->json(),
                //     'data' => $veriffData
                // ], 400);

                return response()->json(['message' => 'Data submission failed','data'=>$response->json()],400);
            }

            return response()->json([
                'success' => true,
                'data' => $response->json()
            ]);
        } catch (\Exception $e) {
            Log::error('Exception in Veriff collected data submission', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Resp::error([
                'success' => false,
                'message' => 'Something went wrong: '.$e->getMessage()
            ], 500);
        }
    }

    public function veriffStatus(Request $request,$id)
    {
        try {
            $veriffData=$request->input('data');
            $data=json_encode($veriffData);
            $baseUrl = config('services.veriff.base_url');
            $signature = hash_hmac(
                'sha256',
                $data,
                config('services.veriff.secret')
            );
            $response = Http::withHeaders([
                'X-AUTH-CLIENT' => config('services.veriff.key'),
                'X-HMAC-SIGNATURE' => $signature
            ])->patch($baseUrl.'/v1/sessions/'.$id,$veriffData);

            if ($response->failed()) {
                //return Resp::error(['message' => 'Something went wrong.'.$response->json(),'data' => $veriffData]);
                return response()->json(['message' => 'Something went wrong.','data'=>$response->json()],400);
                //return $this->fallbackLogic($request);
            }

            return response()->json([
                'success' => true,
                'data' => $response->json()
            ]);
        } catch (\Exception $e) {
            
            return Resp::error([
                'success' => false,
                'message' => 'Something went wrong: '.$e->getMessage()
            ], 500);
        }
    }

    public function VeriffDecision(Request $request,$id){
        try{


            $baseUrl = config('services.veriff.base_url');
            $signature = hash_hmac(
                'sha256',
                $id,
                config('services.veriff.secret')
            );
            $response = Http::withHeaders([
                'X-AUTH-CLIENT' => config('services.veriff.key'),
                'X-HMAC-SIGNATURE' => $signature
            ])->get($baseUrl.'/v1/sessions/'.$id.'/decision');
            if($response->failed()){
                return Resp::error(['message' => 'Something went wrong.'.$response->json(),'data' => $id]);
               
                //return $this->fallbackLogic($request);
            }
            return Resp::json(['data' => $response->json()]);
        }catch(\Exception $e){
            return Resp::error(['message' => 'Something went wrong.'.$e->getMessage()]);
        }
    }

    public function veriffWebhook(Request $request){
        try {

            $data=$request->all();
            Log::info('Veriff decisionWebhook triggered');
            Log::info($request->all());


        $verification = $data['verification'];
        if(!isset($verification)){
            Log::info('verification field not found in data');
        }

        // Check if code equals 9001 and status is "approved"
        if (isset($verification['code']) &&
            isset($verification['vendorData']) && 
            isset($verification['status']) && 
            $verification['code'] == 9001 && 
            $verification['status'] == "approved" ) {

            // For example, let's assume you use 'attemptId' to find the record
            $record = Verify::where('escort_id', $verification['vendorData'])->first();

            if ($record) {
                // Update fields as needed. For instance, update the status and approval time.
                $record->verified_status = 1;
                $record->save();

                Log::info('Record updated successfully for user ');
                return response()->json(['message' => 'Record updated successfully'], 200);
            } else {
                //Log::info("User not found !!");
                Log::warning('Record not found!!');
                return response()->json(['message' => 'Record not found'], 404);
            }
            }

            Log::info("Webhook processed successfully");
            // Return 200 OK to acknowledge receipt
            return response()->json([
                'success' => true,
                'message' => 'Webhook processed successfully',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            
            return Resp::error([
                'message' => 'Webhook processing failed: ' . $e->getMessage()
            ]);
        }
    }

    public function veriffEventWebhook(Request $request){
        try {
            $data=$request->all();
            Log::info("Event webhook triggered");
            Log::info($request->all());

            // if($data && isset($data['action'])){
            //     if($data['action'] == "submitted" && $data['code'] == 7002){
            //         $record = Verify::where('escort_id', $data['vendorData'])->first();
            //         $profile = Profile::where('escort_id', $data['vendorData'])->first();
            //         if($record){
            //             $record->verified_status = 1;
            //             $record->action = $data['action'];
            //             $record->save();
            //             if($profile){
            //                 $profile->verified_status=1;
            //                 $profile->save();
            //                 Log::info("Profile verified status updated");
            //             }
            //             Log::info("Record updated successfully");
            //         }else{
            //             $record = new Verify();
            //             $record->escort_id = $data['vendorData'];
            //             $record->verified_status = 1;
            //             $record->action = $data['action'];
            //             $record->save();
            //             if($profile){
            //                 $profile->verified_status=1;
            //                 $profile->save();
            //                 Log::info("Profile verified status updated");
            //             }
            //             Log::info("Record Created and updated successfully!");
            //         }

            //     }else{
            //         $record=Verify::where('escort_id', $data['vendorData'])->first();
            //         if($record){
            //             $record->action = $data['action'];
            //             $record->save();
            //             Log::info("Record updated successfully");
            //         }
            //     }
            // }
            Log::info("Event webhook processed successfully");
            return Resp::json(['data' => $request->all()]);
        }catch(\Exception $e){
            Log::error("Event webhook processing failed: " . $e->getMessage());
            return Resp::error(['message' => 'Webhook processing failed: ' . $e->getMessage()]);
        }
    }

    //Veriff decision webhook to auto verify user
    public function veriffFullAutoWebhook(Request $request){
        try {

            $data=$request->all();
            Log::info('Veriff fullAuto decision Webhook triggered');
            Log::info($data);


        $verification = $data['data']['verification'];
        if(!isset($verification)){
            Log::info('verification field not found in data');
        }

        // Check if decision is "approved"
        if ( $verification['decision'] == "approved" ) {

            // For example, let's assume you use 'attemptId' to find the record
            $record = Verify::where('escort_id', $data['vendorData'])->first();
            if(!$record){
                $record = new Verify();
                $record->escort_id = $data['vendorData'];
                $record->verified_status = 1;
                // $record->action = $data['action'];
                $record->save();
            }
            $profile = Profile::where('escort_id', $data['vendorData'])->first();
            if ($record && $profile) {
                // Update fields as needed. For instance, update the status and approval time.
                $record->verified_status = 1;
                $record->save();

                $profile->verified_status = 1;
                $profile->save();

                Log::info('User successfully verified!');
                return response()->json(['message' => 'Record updated successfully'], 200);
            } else {
                //Log::info("User not found !!");
                Log::warning('Record not found!!');
                return response()->json(['message' => 'Record not found'], 404);
            }
        }

            Log::info("Webhook processed successfully");
            // Return 200 OK to acknowledge receipt
            return response()->json([
                'success' => true,
                'message' => 'Webhook processed successfully',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error("Webhook processing failed: " . $e->getMessage());
            return Resp::error([
                'message' => 'Webhook processing failed: ' . $e->getMessage()
            ]);
        }
    }


}
