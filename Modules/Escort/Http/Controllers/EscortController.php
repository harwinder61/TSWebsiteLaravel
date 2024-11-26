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


class EscortController extends Controller
{
    public function __construct()
    {
        //$this->middleware('jwtauth');
        //$this->middleware(AuthMiddleware::class);
    } 

    public function getActiveSubscription(Request $request)
    {
        $user = auth()->user();
        $subscriptions = EscortSubscription::where('escort_id', $user->id)
            ->where('status', 'active')
            ->get(); // Changed from first() to get() to retrieve all active subscriptions
        $profile = Profile::where('escort_id', $user->id)->first();
        $media = Media::where('escort_id', $user->id)->get();
        
        return Resp::success([
            'subscriptions' => $subscriptions, // Changed key from singular to plural
        ]);
    }
   public function profileViews( $id,Request $request)
   {
    $user = auth()->user();
    $profile = Profile::where('escort_id', $user->id)->first();
    $profile->profile_views++;
    $profile->save();
    return Resp::success(['message' => 'Profile views updated successfully']);
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
            $user->is_hidden = true;
            $user->save();
            
            return Resp::success(['message' => 'Profile hidden successfully']);
        }
        
        return Resp::success(['message' => 'Profile ' . ($request->is_hidden ? 'hidden' : 'unhidden') . ' successfully']);
    }

    public function deleteProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'is_delete' => 'required|boolean'
        ]);

    if ($validator->fails()) {
        return Resp::fieldErrors(['field_errors' => $validator->errors()]);
    }

    $user = auth()->user();
    // Only update if is_delete is true
    if ($request->is_delete) {
        $user->delete_on = now();
        $user->is_delete = true; 
        $user->save();
        
        return Resp::success(['message' => 'Profile deleted successfully']);
    }
    
        return Resp::error(['message' => 'Invalid request']);
    }
    
    public function updateSubscription(Request $request)
{
    $validator = Validator::make($request->all(), [
        'subscription_id' => 'required|exists:subscriptions,id',
        'image_id' => 'required|exists:media,id'
    ]);

    if ($validator->fails()) {
        return Resp::fieldErrors(['field_errors' => $validator->errors()]);
    }
    $user = auth()->user();
    $subscription = EscortSubscription::find($request->subscription_id);
    
    if (!$subscription) {
        return Resp::error(['message' => 'Subscription not found'], 404);
    }
    $subscription->update([
        'image_id' => $request->image_id
    ]);
    return Resp::success([
        'message' => 'Subscription updated successfully',
        'subscription' => $subscription
        ]);
    }

    
    public function updateMedia(Request $request)
{
    $validator = Validator::make($request->all(), [
        'gallery' => 'array',                   
        'gallery.*' => 'exists:media,id',      
        'private_gallery' => 'array',            // Private gallery should be an array
        'private_gallery.*' => 'exists:media,id', // Each private gallery item should exist in media table
        'promo_video' => 'exists:media,id'       // Promo video should exist in media table
    ]);

    // Return validation errors if any
    if ($validator->fails()) {
        return Resp::fieldErrors(['field_errors' => $validator->errors()]);
    }

    $user = auth()->user();
    if ($request->has('gallery')) {
        Media::where('escort_id', $user->id)
            ->where('type', 'gallery')
            ->whereIn('id', $request->input('gallery'))
            ->update(['is_temp' => false]);

        Media::where('escort_id', $user->id)
            ->where('type', 'gallery')
            ->whereNotIn('id', $request->input('gallery'))
            ->forceDelete();
    }

    if ($request->has('private_gallery')) {
        Media::where('escort_id', $user->id)
            ->where('type', 'private_gallery')
            ->whereIn('id', $request->input('private_gallery'))
            ->update(['is_temp' => false]);


        Media::where('escort_id', $user->id)
            ->where('type', 'private_gallery')
            ->whereNotIn('id', $request->input('private_gallery'))
            ->forceDelete();
    }

    if ($request->has('promo_video')) {
        Media::where('escort_id', $user->id)
            ->where('type', 'promo_video')
            ->where('id', $request->input('promo_video'))
            ->update(['is_temp' => false]);

        Media::where('escort_id', $user->id)
            ->where('type', 'promo_video')
            ->whereNotIn('id', [$request->input('promo_video')])
            ->forceDelete();
    }

    return Resp::success(['message' => 'Media updated successfully']);
}
    

    public function getEscortProfile($id,Request $request)
    {
        $user = auth()->user();
        $profile = Profile::where('escort_id', $user->id)->first();
        $media = Media::where('escort_id', $user->id)->get();
        if ($profile) {
            $profile = Profile::where('escort_id', $user->id)->first();
            return Resp::success([
                'id' => $user->id,
                'profile' => $profile,
                'media' => $media
            ]);
        }
        return Resp::error(['message' => 'No active subscription found'], 404);
    }

    public function inquiryForm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|in:' . implode(',', InqueryFormSubject::getValues()),
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
        $profile_data = Profile::find($user->id);
        $profile_data->county;
        $profile_data->region;
        $profile_data->city;
        $profile_data->rates;
        if (!$profile_data) {

            return Resp::error(['message' => 'No profile found'], 404);
        }
        return Resp::success(['list' => $profile_data]);
    }



    public function update(Profile $profile, Request $request)
    {

        $user = auth()->user();
        $userType = $user->user_type;

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
            ]);
            if (!$updated) {
                return Resp::error(['error' => 'Failed to update profile'], 500);
            }

            $profile_data = Profile::where('escort_id', $user->id)->first();

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

            $profile_rates = ProfileRates::where('escort_id', $profile_data->id)->get();
            $rates_data = $request->input('rates');
            if (!$profile_rates) {
                $profile_rates = ProfileRates::create([
                    'escort_id' => $profile_data->id,
                ]);
            }
            foreach ($rates_data as $rate) {
                $category = strtolower($rate['category']);
                $profile_rates = ProfileRates::where('escort_id', $user->id)
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
                        $rate_data['escort_id'] = $user->id;
                        ProfileRates::create($rate_data);
                    }
                }
            }
            $profile_data = Profile::where('escort_id', $user->id)->first();
            $profile_data->rates;
            return Resp::success(['details' => $profile_data]);
        } else {
            return Resp::error(['Invalid user type']);
        }
        return Resp::error(['No user type found']);
    }


}
