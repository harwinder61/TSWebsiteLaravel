<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Resp;
use Illuminate\Support\Facades\Validator;
use Modules\Escort\app\Models\Profile;
use Illuminate\Support\Facades\Response;
use Modules\Auth\app\Models\AuthUser;

class FanController extends Controller
{
    public function getFans(Request $request){
        $fans=AuthUser::with('profile')->where('user_type',1)->get();
        return Resp::success(['details'=>$fans]);
    }

    public function updateProfile($id,Profile $profile,Request $request){
        $admin=auth()->user();
        if($admin->user_type!=3){
            return Resp::error(['Unauthorized user is not an admin']);
        }


        $validator = Validator::make($request->all(), $profile->rules());

            if ($validator->fails()) {
                return Resp::error(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
                }

            $user_id=$id;
            $user_exists=AuthUser::find($user_id);
            if(!$user_exists){
                return Resp::error(['Profile not found']);
            }
            //$profile = Profile::where('escort_id', $user_id)->first();\
            $profile=AuthUser::find($user_id)->profile;

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
            //$data = Profile::where('escort_id', $user_id)->get();
            $profile_data = Profile::where('escort_id', $user_id)->first();

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
                $profile_rates = ProfileRates::where('escort_id', $user_id)
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
                        $rate_data['escort_id'] = $user_id;
                        ProfileRates::create($rate_data);

                    }
                }
            }
           $profile_data=Profile::where('escort_id', $user_id)->first();
           $profile_data->rates;
            return Resp::success(['details'=>$profile_data]);
    }
}
