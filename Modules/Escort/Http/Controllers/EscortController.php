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
use App\Models\Nationality;
use App\Models\AddGallary;
use App\Services\Resp;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Entities\User;
class EscortController extends Controller
{
    public function __construct()
    {
        $this->middleware(AuthMiddleware::class);
    }
    


    public function find(Request $request){
        //get user profile

        $user=auth()->user();
        $profile_data=Profile::find($user->id);
        $profile_data->rates;
        if(!$profile_data){

            return Resp::error(['message' => 'No profile found'], 404);
        }
        return Resp::success(['details' => $profile_data]);
    }

    

    public function update(Profile $profile ,Request $request){

        $user=auth()->user();
        // Get the user type
        $userType = $user->user_type;

        // Fetch user data based on user type
        if ($userType == 1) {
            return Resp::error($user); 
        } elseif ($userType == 2) {

            $validator = Validator::make($request->all(), $profile->rules());

            if ($validator->fails()) {
                return Resp::error(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
                }

            $user_id=$user->id;
            //$profile = Profile::where('escort_id', $user_id)->first();\
            $profile=User::find($user_id)->profile;

            if (!$profile) {
                return Response::json(['error' => 'Profile not found'], 404);
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
            ]);
            if (!$updated) {
                return Resp::error(['error' => 'Failed to update profile'], 500);
            }
            // Find the updated escort profile
            //$data = Profile::where('escort_id', $user->id)->get();
            $profile_data = Profile::where('escort_id', $user->id)->first();

            $is_incall_enabled=$request->input('is_incall_enabled');
            $is_outcall_enabled=$request->input('is_outcall_enabled');
            // Define base validation rules
            $baseRules = [
                'rates' => 'required|array',
                
            ];
            $customMessages=[];
            $rateFields=['15_min', '30_min', '1_hour', '2_hour', '4_hour', 'overnight'];

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

        // Validate request data
        $validator = Validator::make($request->all(), $baseRules,$customMessages);

        if ($validator->fails()) {
 
            return Response::json(['error' => $validator->errors()], 422);
        }
            


            $profile_rates=ProfileRates::where('escort_id', $profile_data->id)->get();
            $rates_data=$request->input('rates');
            if(!$profile_rates){
                $profile_rates=ProfileRates::create([
                    'escort_id'=>$profile_data->id,
                ]);
            }
            foreach($rates_data as $rate){
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
           $profile_data=Profile::where('escort_id', $user->id)->first();
           $profile_data->rates;
            return Resp::success(['details'=>$profile_data]);
        } else {
            return Resp::error(['Invalid user type']);
        }

        return Resp::error(['No user type found']);
    }

}


