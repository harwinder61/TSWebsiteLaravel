<?php

namespace Modules\Escort\Http\Controllers;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Escort\app\Models\Profile;
use Modules\Escort\app\Models\ProfileRates;
use Illuminate\Support\Facades\Response;
use Modules\Auth\app\Http\Middleware\AuthMiddleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class EscortController extends Controller
{
    public function __construct(){
        $this->middleware(AuthMiddleware::class);
    }
    


    public function getProfile(Request $request){

        $user=auth()->user();
        $data=Profile::where('escort_id',$user->id)->first();
        $profile_rates=ProfileRates::where('escort_id',$user->id)->get();
        //if($data->isEmpty()){
        //    return Response::json(['message'=>'No profile found'],404);
        //}
        Log::info("get Profile function here");
        if(!$data){

            return Response::json(['message'=>'No profile found'],404);
        }
        

        return Response::json(['profile'=>$data,'rates'=>$profile_rates]);


    }

    

    public function updateProfile(Profile $profile ,Request $request){

        $user=auth()->user();
        // Get the user type
        $userType = $user->user_type;

        // Fetch user data based on user type
        if ($userType == 1) {
            return Response::json(['msg'=>'User type 1 does not have access to update profile','user'=>$user]);
            
        } elseif ($userType == 2) {
            Log::info("Update profile function here---------------------------------------------------------");
            $validator = Validator::make($request->all(), $profile->rules());

            if ($validator->fails()) {
                return Response::json(['error' => $validator->errors()], 422);
            }

            $user_id=$user->id;
            $profile = Profile::where('escort_id', $user_id)->first();
            if (!$profile) {
                return Response::json(['error' => 'Profile not found'], 404);
            }
            //$languages = json_encode($request->input('languages'));
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

                return Response::json(['error' => 'Failed to update profile','current_user'=>$user], 500);
            }
            // Find the updated escort profile
            //$data = Profile::where('escort_id', $user->id)->get();
            $data = Profile::where('escort_id', $user->id)->first();

            $is_incall_enabled=$request->input('is_incall_enabled');
            $is_outcall_enabled=$request->input('is_outcall_enabled');
            // Define base validation rules
            $baseRules = [
                'rates' => 'required|array',
                
            ];
            $customMessages=[];
            $rateFields=['category','15_min', '30_min', '1_hour', '2_hour', '4_hour', 'overnight'];

            // Add validation for rate fields
        //foreach ($rateFields as $field) {
         //   $baseRules["rates.*.{$field}"] = [
          //      'required',
           // ];
            
        //}

        // Add conditional rules based on enabled services
        if ($is_incall_enabled) {
            foreach ($rateFields as $field) {
                $baseRules["rates.*.{$field}"] = [
                    'required',
                ];
                $customMessages["rates.*.{$field}.required"] = "The {$field} field is required for Incall rates.";
                

            }
        }

        if ($is_outcall_enabled) {
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
            


            $profile_rates=ProfileRates::where('escort_id', $user->id)->first();
            $rates_data=$request->input('rates');
            Log::info("start of the loop");
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
                        Log::info("Rates {$category} data: updating");
                    } else {
                        $rate_data['escort_id'] = $user->id;
                        ProfileRates::create($rate_data);
                        Log::info("Rates {$category} data: creating");
                    }
                }
            }
            //$updated_profile_rates=$profile_rates->update([
            //    'category'=>$request->input('category'),
            //    '15_min'=>$request->input('15_min'),
            //    '30_min'=>$request->input('30_min'),
            //    '1_hour'=>$request->input('1_hour'),
            //    '2_hour'=>$request->input('2_hour'),
            //    '4_hour'=>$request->input('4_hour'),
            //    'overnight'=>$request->input('overnight'),
            //]);

            Log::info("Updating profile rates --------------");
            //if(!$updated_profile_rates){
            //    Log::error('Profile rates update failed------------');
            //    return Response::json(['error'=>'Failed to update profile rates']);
            //}
            //$data=Escort::find($user->id);
            //$profile_rates=ProfileRates::where('escort_id', $user->id)->get();
            //$profile_rates=ProfileRates::where('escort_id', $user->id)->first();
            return Response::json(['msg'=>'profile updated successfully','data'=>$data]);
        } else {
            return Response::json(['msg'=>'Invalid user type','user'=>$user]);
            
        }

        
        return Response::json(['msg'=>'No user type found','user'=>$user]);
    }
}


