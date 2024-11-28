<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BaseReviews;
use Illuminate\Http\Request;
use Modules\Admin\app\Models\Plan;
use App\Services\Resp;
use Illuminate\Support\Facades\Validator;
use Modules\Escort\app\Models\Profile;
use Modules\Escort\app\Models\ProfileRates;
use Illuminate\Support\Facades\Response;
use Modules\Auth\app\Models\AuthUser;
use Modules\Escort\app\Models\Inquiry;
use Modules\Admin\app\Models\Permissions;
use Illuminate\Support\Facades\Mail;
use App\Services\EmailService as Mailer;
use App\Models\Location;
use App\Models\Subscription as subscriptions;
use Modules\Escort\app\Models\Subscription;
use Illuminate\Support\Facades\Log;
use App\Models\Image;
use App\Models\Media;
use Illuminate\Support\Facades\File;
use Stripe\Service\SubscriptionService;
use App\Models\User;
use Modules\Admin\app\Models\Blog;

class AdminController extends Controller    
{
    
 
    
    public function recentPurchases(Request $request){
        $purchases=Subscription::orderBy('created_at','desc')->get();
        return Resp::success(['list'=>$purchases]);
    }

    public function blog(Request $request){
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'media_id' => 'required|exists:media,id',
            'date' => 'required|date',
        ]);
        if ($validator->fails()) {
            return Resp::error(['message' => $validator->errors()]);
        }
        $blog=Blog::create($request->all());
        return Resp::success(['message'=>'Blog created successfully']);
    }

    public function spotlightMedia(Request $request)
    {
        
        $subs_data=Subscription::leftJoin('users','users.id','=','subscriptions.escort_id')
                    ->where('plan_code','P104')->where('status','ACTIVE')->get();
        return Resp::success([
            'subscribers' => $subs_data
        ]);
    }
    
    public function updatePlanDetails($plan_code, Request $request)
    {

        $plan = Plan::where('code', $plan_code)->first();
        if (!$plan) {
            
            return Resp::error(['message' => 'Plan not found']);
        }
    
        $validator = Validator::make($request->all(), [
            'price' => 'required|numeric',
            'description' => 'nullable|string',
            'advert_spaces' => 'nullable|integer',
            'checkout_text' => 'nullable|string',
            'desktop_placeholder' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5000000',
            'mobile_placeholder' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5000000'
            
            
        ]);
    
        if ($validator->fails()) {
            return Resp::error(['message' => $validator->errors()]);
        }
    
        $plan->update($request->only(['price', 'description', 'advert_spaces', 'checkout_text']));
        
        // Handle desktop placeholder
        if ($request->hasFile('desktop_placeholder')) {
            $desktopImage = $request->file('desktop_placeholder');
            $desktopImageName = $plan_code . '_desktop_' . time() . '_' . $plan->id . '.' . $desktopImage->getClientOriginalExtension();
            $userFolder = 'uploads/media/plan/' . $plan_code;   
            
            if (!File::isDirectory(public_path($userFolder))) {
                File::makeDirectory(public_path($userFolder), 0755, true);
            }
    
            if ($plan->desktop_placeholder) {
                $oldPath = public_path($plan->desktop_placeholder);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }
            
            $desktopImage->move(public_path($userFolder), $desktopImageName);
            $plan->desktop_placeholder = $userFolder . '/' . $desktopImageName;
        }
    
        // Handle mobile placeholder
        if ($request->hasFile('mobile_placeholder')) {
            $mobileImage = $request->file('mobile_placeholder');
            $mobileImageName = $plan_code . '_mobile_' . time() . '_' . $plan->id . '.' . $mobileImage->getClientOriginalExtension();
            $userFolder = 'uploads/media/plan/' . $plan_code;   
            
            if (!File::isDirectory(public_path($userFolder))) {
                File::makeDirectory(public_path($userFolder), 0755, true);
            }
    
            if ($plan->mobile_placeholder) {
                $oldPath = public_path($plan->mobile_placeholder);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }
            
            $mobileImage->move(public_path($userFolder), $mobileImageName);
            $plan->mobile_placeholder = $userFolder . '/' . $mobileImageName;
        }
    
        $plan->save();
    
        return Resp::success([
            'message' => 'Plan updated successfully', 
            'plan' => $plan
        ]);
    }

    public function userQuickList(Request $request) { 
        $user_type = $request->query('user_type');
        if (!$user_type) {
            $quick_user_list = AuthUser::select('username', 'id')->get();
        } else {
            $quick_user_list = AuthUser::select('username', 'id') 
                             ->where('user_type', $user_type)
                             ->get();
            if ($quick_user_list->isEmpty()) {
                return Resp::error(['message' => 'No users found for this user type']);
            }
        }
        return Resp::success(['list' => $quick_user_list]);
    }


    public function createSubscription(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'plan_code' => 'required|exists:plans,code',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date', 
            'image_id' => 'required|exists:media,id',
        ]);

            if ($validated->fails()) {
            return Resp::error(['message' => $validated->errors()]);
        }
        $media_exists=Media::where('escort_id',$request->input('user_id'))
        ->where('id',$request->input('image_id'))
        ->first();
        if(!$media_exists){
            return Resp::error(['Media not found']);
        }
    
        try {
          
            $plan = Plan::where('code', $request->input('plan_code'))->first();
            $subscription = Subscription::create([
                'escort_id' => $request->input('user_id'),
                'plan_code' => $request->input('plan_code'),
                'status' => 'ACTIVE',
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'created_by' => auth()->user()->id,
                'image_id' => $request->input('image_id'),
                'created_mode' => 'Admin',
               
            ]);
            return Resp::success([

                'message' => 'Subscription created successfully',
                'subscription' => $subscription
            ]);
        } catch (\Exception $e) {
            return Resp::error(['message' => $e->getMessage()]);
        }
    }

public function assignPermissions($id,Request $request){
        $validator = Validator::make($request->all(), [
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'required|integer|min:1|max:100'        
        ]);
        if ($validator->fails()) {
            return Resp::error([$validator->errors()]);
        }
        $user = AuthUser::find($id);
        if (!$user || $user->user_type != 3) {
            return Resp::error(['Invalid user or user type']);
        }
        
        $user->permission_ids = $request->permission_ids;
        $user->save();
        return Resp::success(['message' => 'Permissions assigned successfully']);

}

    public function getPermissions(Request $request){
        $permissions=Permissions::get();
        return Resp::success(['list'=>$permissions]);
    }


    public function inquiryFormList(Request $request){
        $inquiries=Inquiry::orderBy('created_at','desc')->get();
        return Resp::success(['list'=>$inquiries]);
    }

    public function recentSignups(Request $request){
        $users = AuthUser::latest()
            ->when($request->query('user_type'), function($query) use ($request) {
                $query->where('user_type', $request->query('user_type')); 
            })
            ->limit(50)
            ->get(['id', 'username', 'email', 'user_type', 'created_at'])
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'username' => $user->username, 
                    'email' => $user->email,
                    'user_type' => $user->user_type,
                    'created_at' => $user->created_at
                ];
            });
    
        return Resp::success([
            'total_count' => $users->count(),
            'users' => $users
        ]);
    }

    public function updatePlan($plan_code,Request $request){
        
        $validator=Validator::make($request->all(),[
            'title'=>'string|required',
            'price'=>'decimal:2|required',
            'description'=>'required',
            'days'=>'integer|required',
            'allowed_user_account'=>'integer|required',
        ]);
        if($validator->fails()){
            return Resp::error([$validator->errors()]);
        }
        $code=$plan_code;
        $plan=Plan::where('code',$code)->first();
        if(!$plan){
            return Resp::error(['Plan not found']);
        }
        $updated_plan=$plan->update([
            'title'=>$request->title,
            'price'=>$request->price,
            'description'=>$request->description,
            'days'=>$request->days,
            'allowed_user_account'=>$request->allowed_user_account,
        ]);
        if(!$updated_plan){
            return Resp::error(['Failed to update plan']);
        }
        $updated_plan=Plan::where('code',$code)->first();
        return Resp::success(['details'=>$updated_plan]);

    }

    public function getPlan($id,Request $request){
        
        
        $plan=Plan::where('code',$id)->first();
        if(!$plan){
            return Resp::error(['Plan not found']);
        }
        return Resp::success(['details'=>$plan]);
    }

    public function updateProfile($id,Profile $profile,Request $request){
        $admin=auth()->user();

        if($admin->user_type!=3){
            return Resp::error(['Unauthorized user is not an admin']);
        }

        $request_data=$request->all();

        $validator = Validator::make($request->all(), $profile->rules());

        if ($validator->fails()) {
                return Resp::error(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
            }


            $user_id=$id;
            $user_exists=AuthUser::find($user_id);
            if(!$user_exists){
                return Resp::error(['Profile not found']);
            }

            $profile=AuthUser::find($user_id)->profile;

            if (!$profile) {
                return Response::json(['error' => 'Profile not found'], 404);
            }

            $city_id=$request->input('city_id');
            $city_exists=Location::where('id',$city_id)->where('type','city')->first();

            $county_id=$city_exists->parent_id;
            $county_exists=Location::where('id',$county_id)->where('type','county')->first();
            if(!$county_exists){
                return Resp::error(['County not found']);
            }
            $region_id=$county_exists->parent_id;
            $region_exists=Location::where('id',$region_id)->where('type','region')->first();
            if(!$region_exists){
                return Resp::error(['Region not found']);
            }

            $whatsapp_number=0;
            $country_code=0;
            $allow_whatsapp=$request->input('allow_whatsapp');
            if($allow_whatsapp){
                $whatsapp_number=$request->input('whatsapp_number');
                $country_code=$request->input('country_code');
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
                'is_incall_enabled' => $request->input('is_incall_enabled'),
                'is_outcall_enabled' => $request->input('is_outcall_enabled'),
                'has_onlyfans' => $request->input('has_onlyfans'),
                'has_manyvids' => $request->input('has_manyvids'),
                'has_fancentro' => $request->input('has_fancentro'),
                'onlyfans_handle' => $request->input('onlyfans_handle'),
                'manyvids_handle' => $request->input('manyvids_handle'),
                'fancentro_handle' => $request->input('fancentro_handle'),
                'city_id' => $city_id,
                'region_id' =>$region_id,
                'county_id' => $county_id,
                'allow_whatsapp' => $allow_whatsapp,
                'whatsapp_number' => $whatsapp_number,
                'country_code' => $country_code,
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
 
            return Response::json(['error' => $validator->errors()], );
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

    
    public function getProfile($id){
        $profile=AuthUser::with('profile')->find($id);
        if(!$profile){
            return Resp::error(['Profile not found']);
        }
        $profile->profile->rates;
        return Resp::success(['details'=>$profile]);
    }

    
    public function getUsers(Request $request){
        $user_type=$request->query('user_type');
        $users=AuthUser::query();
        $users=$users->whereIn('user_type', [$user_type, 0]);
        $users=$users->leftJoin('subscriptions','subscriptions.escort_id','=','users.id');
        //$users=$users->where('user_type', 1);
        // Pagination parameters
        $perPage = $request->query('per_page', 10); 
        $page = $request->query('page', 1); 
        $offset = ($page - 1) * $perPage;

        // Get total count for pagination info
        $totalCount = $users->count();

        // Fetch the results with offset and limit
        $result = $users->offset($offset)
            ->limit($perPage)
            ->get();
        return Resp::success(['list'=>$result,'total_count'=>$totalCount,'page'=>$page,'per_page'=>$perPage]);
    }
}


