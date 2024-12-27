<?php

namespace Modules\Escort\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Services\Resp;
use Modules\Auth\app\Models\AuthUser;
use Modules\Escort\app\Http\Middleware\AuthEscort;
use Modules\Escort\app\Models\Orders;
use Modules\Escort\app\Models\Subscription;
use App\Models\Plan;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Checkout\Session;
use Illuminate\Support\Facades\Mail;
use Modules\Escort\app\Mail\OrderPaidNotification;
use App\Services\EmailService;
use App\Models\Media;
use App\Models\Location;
use App\Mail\EmailHelper;
use Modules\Admin\app\Models\EmailTemplates;






class OrderController extends Controller
{
    public function __construct()
    {

    }

    function createOrder(Request $request){
        $user=auth()->user();
        $image_id=0;
        if($request->input('image_id')){
            $image_id=$request->input('image_id');
        }
        
        $validator = Validator::make($request->all(), [
            'plan_code' => 'required|string|exists:plans,code',
            'start_date' => 'required|date',
            'payment_status' => 'required|string|in:PENDING,PAID',
            'only_fans_link' => 'nullable|string',
            'many_vids_link' => 'nullable|string',
            'fan_centro_link' => 'nullable|string',
            'image_id' => 'required|exists:media,id',
            'extra_locations' => 'nullable|array',
            'extra_locations.*' => 'exists:locations,id',
        ]);
    
        if ($validator->fails()) {
            return Resp::fieldErrors(['field_errors' => $validator->errors()]);
        }
    
        $plan = Plan::where('code', $request->input('plan_code'))->first();
        if (!$plan) {
            return Resp::success(['error'=> 'Plan not found']);
        }

        $sub_exists=Subscription::where('escort_id',$user->id)->first();
        $days = $plan->days;
        $end_date = date('Y-m-d', strtotime($request->input('start_date') . " + $days days"));
        

        $subscription_count = Subscription::where('plan_code', $request->input('plan_code'))
            ->where('status', 'ACTIVE')
            ->get()->count();
    
        $fiveMinutesAgo = now()->subMinutes(5)->toDateTimeString();
        $pendingOrders = Orders::where('payment_status', 'PENDING')
            ->where('created_at', '>=', $fiveMinutesAgo)
            ->get();
    
        $pending_orders_count = $pendingOrders->count();
    
        $max_users = Plan::where('code', $request->input('plan_code'))->first('allowed_user_account');
        $max_users = $max_users->allowed_user_account;
    
        $total_orders_count = $subscription_count + $pending_orders_count;
    
        if ($total_orders_count >= $max_users) {
            return Resp::success(['error'=> 'Max subscription reached, plan not available']);
        }
    
        $start_date2 = Carbon::parse($request->input('start_date'));
        $end_date2 = Carbon::parse($end_date);
    
        $weekly_sub_exists = null;
        if ($request->input('plan_code') == "P101") {
            $weekly_sub_exists = Subscription::where('plan_code', $request->input('plan_code'))
                ->where('status', 'ACTIVE')
                ->where(function ($query) use ($start_date2, $end_date2) {
                    $query->where(function ($q) use ($start_date2, $end_date2) {
                        $q->where('start_date', '<=', $end_date2)
                            ->where('end_date', '>=', $start_date2);
                    })
                    ->orWhere(function ($q) use ($start_date2, $end_date2) {
                        $q->whereBetween('start_date', [$start_date2, $end_date2]);
                    });
                })->get();
    
            if ($weekly_sub_exists->isNotEmpty()) {
                return Resp::success(['error'=> 'Weekly subscription is already owned by someone']);
            }
        }
    
        $order = Orders::create([
            'escort_id' => $user->id,
            'plan_code' => $request->input('plan_code'),
            'start_date' => $request->input('start_date'),
            'end_date' => $end_date,
            'payment_status' => 'PENDING',
            'only_fans_link' => $request->input('only_fans_link'),  
            'many_vids_link' => $request->input('many_vids_link'),  
            'fan_centro_link' => $request->input('fan_centro_link'), 
            'image_id' => $request->input('image_id'),
            'extra_location' => $request->input('extra_locations'),
            
        ]);
        
        if (!$order) {
            return Resp::success(['error'=> 'Failed to create order']);
        }
    
        // Stripe payment logic remains the same
        $session_url = "";
        try {
            // Set the Stripe secret key
            Stripe::setVerifySslCerts(false);
            Stripe::setApiKey(env('STRIPE_SECRET'));
    
            $plan = Plan::where('code', $request->input('plan_code'))->first();
            $extra_locations_price=0;
            $price=$plan->price;
            if($request->input('plan_code')=="P105" && !$sub_exists){
                $price=0;
            }

            $amount = intval($plan->price) * 100;
            $title = $plan->title;
            
            $paymentIntent = PaymentIntent::create([
                'amount' => $amount, // amount in cents
                'currency' => 'eur',
                'metadata' => ['order_id' => $order->id],
            ]);
        } catch (\Exception $e) {
            return Resp::error([$e->getMessage()]);
        }
        $response = [
            'order_id'=>$order->id,
            'client_secret' => $paymentIntent->client_secret,
            'dpmCheckerLink' => "https://dashboard.stripe.com/settings/payment_methods/review?transaction_id={$paymentIntent->id}",
        ];
    
// ... existing code ...

$media = Media::where('id', $request->input('image_id'))
    ->where('escort_id', $user->id)
    ->first();

if (!$media || $media->id != $request->input('image_id')) {
    return Resp::success(['error'=> 'Invalid image id']);
}

      
        if ($request->has('only_fans_link')) {
            $response['only_fans_link'] = $request->input('only_fans_link');
        }
        if ($request->has('many_vids_link')) {
            $response['many_vids_link'] = $request->input('many_vids_link');
        }
        if ($request->has('fan_centro_link')) {
            $response['fan_centro_link'] = $request->input('fan_centro_link');
        }
       EmailHelper::sendDynamicEmail('new_order', 
    ['[CUSTOMER_NAME]' => $user->username, '[CUSTOMER_EMAIL]' => $user->email], 
    $user->email);
        return Resp::success($response);
    }

    function updateOrder(Request $request){
        $user=auth()->user();
        $order_id=$request->input('order_id');
        $validator=Validator::make($request->all(),[
            'order_id'=>'required|string|exists:orders,id',
        ]);
        if($validator->fails()){
            return Resp::error([$validator->errors()]);
        }
        $order=Orders::find($order_id);
        if(!$order){
            return Resp::error(['Order not found']);
        }

        $plan = Plan::where('code', $request->input('plan_code'))->first();
        if (!$plan) {
            return Resp::error(['Plan not found']);
        }
    
        $days = $plan->days;
        $end_date = date('Y-m-d', strtotime($request->input('start_date') . " + $days days"));

        $updated_order=Orders::where('id',$order_id)->update([
            'image_id'=>$request->input('image_id'),
            'start_date'=>$request->input('start_date'),
            'end_date'=>$end_date,
            'only_fans_link'=>$request->input('only_fans_link')??'',
            'many_vids_link'=>$request->input('many_vids_link')??'',
            'fan_centro_link'=>$request->input('fan_centro_link')??'',
        ]);
        if(!$updated_order){
            return Resp::error(['Failed to update order']);
        }
        return Resp::success(['Order updated successfully']);
    }
    function webhook_payment_status_update(Request $request){
        
        $order_id=$request->input('order_id');
        $extra_location=$request->input('extra_locations');
        $validator=Validator::make($request->all(),[
            'order_id'=>'required|string|exists:orders,id',
            'extra_locations'=>'nullable|array',
            'extra_locations.*'=>'exists:locations,id'
        ]);
        if($validator->fails()){
            return Resp::error([$validator->errors()]);
        }
        $order=Orders::find($order_id);

        if(!$order){
            return Resp::error(['Order not found']);
        }
        if($order->payment_status=="PENDING"){
            $order->update([
                'payment_status'=>'PAID',
            ]);
            $escort=AuthUser::find($order->escort_id);
            $email = new EmailService ();  
            $email->to($escort->email);
            $email->subject('Thanku for purchasing subscription hope you enjoy our services');
            $email->setBodyPurchasingEmail('purchasing',['user' => $order->escort_id]);
            $email->send();

            $plan=Plan::where('code',$order->plan_code)->first();
            if(!$plan){
                return Resp::error(['Plan not found']);
            }
            $days=$plan->days;
            

            //$subscription_exists=Subscription::where('escort_id',$order->escort_id)
            //    ->where('plan_code',$order->plan_code)
            //    ->where('status','ACTIVE')
            //    ->first();
                

            //if($subscription_exists){
            //    return Resp::error(['Subscription already exists']);
            //}
            Log::info("Number of days from plans table : ".$days);
            // Ensure the start_date is in the correct format
            // Ensure the start_date is in the correct format
            $start_date = Carbon::parse($order->start_date);
            $end_date = $start_date->addDays($days)->subDay()->format('Y-m-d'); // Subtract one day from the calculated end date
            //old end_date calculation
            //$end_date = $start_date->addDays($days)->format('Y-m-d'); // Correctly calculate end date
           
            Log::info("End date : ".$end_date);
            Log::info("Start date : ".$order->start_date);
            $subscription=Subscription::create([
                'escort_id'=>$order->escort_id,
                'order_id'=>$order->id,
                'plan_code'=>$order->plan_code,
                'start_date'=>$order->start_date,
                'image_id'=>$order->image_id,
                'status'=>'ACTIVE',
                'end_date'=>$end_date,
                'extra_location'=>$extra_location
            ]);
            if(!$subscription){
                return Resp::error(['Failed to create subscription']);
            }
            return Resp::success(['subscription'=>$subscription,'order'=>$order]);
        }else{
          return Resp::error(['Order already paid']);
        }
        return Resp::success(['order'=>$order]);
    }

    function getSubscription() {
        $user = auth()->user();
        
        $subscription = Subscription::where('escort_id', $user->id)
            ->where('status', 'ACTIVE')
            ->get(); 
        return Resp::success(["list" => $subscription]);
    }  

    function getLocationAndSubscriptions(Request $request){
        if(!$request->query('s')){
            return Resp::success(["locations" => [],"subscriptions" => []]);

        }
        $search=$request->query('s');
        $location = Location::with('county')->where('name', 'LIKE', '%'.$search.'%')->get();
        
        $subscriptions = Subscription::with('escort.profile','media','escort.profile.city','escort.profile.region','escort.profile.county')->whereHas('escort.profile', function($query) use ($search) {
            $query->where('name', 'LIKE', '%'.$search.'%');
        })
        ->get();
        
        return Resp::success(["locations" => $location,"subscriptions" => $subscriptions]);
    }
    public function getEscortPreviousSubscriptions(Request $request){
        $user=auth()->user();
        $subscriptions=Subscription::where('escort_id',$user->id)
            ->where('status','ACTIVE')
            ->where('plan_code','P101')
            ->where('start_date','>',date('Y-m-d'))
            ->get();
        return Resp::success(['subscriptions'=>$subscriptions]);
    }

    public function getLatestEscortSubscription(Request $request){
        $user=auth()->user();
        $subscription=Subscription::with('orders')->where('escort_id',$user->id)->orderBy('id','desc')->first();
        if(!$subscription){
            return Resp::error(['Subscription not found']);
        }
        return Resp::success(['data'=>$subscription]);
    }

    public function updateLatestEscortSubscription(Request $request){
        $user=auth()->user();
        $sub_id=$request->input('subscription_id');
        $subscription=Subscription::find($sub_id);
        
        if(!$subscription){
            return Resp::error(['Subscription not found']);
        }


    $updateData = [];
    if ($request->has('extra_locations')) {
        $updated_locations=$subscription->update(['extra_location'=>$request->input('extra_locations')]);
        if(!$updated_locations){
            return Resp::error(['Failed to update extra locations']);
        }
    }
    if ($request->has('onlyfans_link')) {
        $updateData['only_fans_link'] = $request->input('onlyfans_link');
    }
    if ($request->has('manyvids_link')) {
        $updateData['many_vids_link'] = $request->input('manyvids_link');
    }
    if ($request->has('fancentro_link')) {
        $updateData['fan_centro_link'] = $request->input('fancentro_link');
    }

    $updated_subscription = $subscription->orders->update($updateData);
    if(!$updated_subscription){
        return Resp::error(['Failed to update subscription']);
    }


        return Resp::success(['data'=>$subscription]);
    }


    public function locationIdsToLocationNames(Request $request){
        
        $ids=$request->input("location_ids");
        $locations=Location::whereIn('id',$ids)->get();
        return Resp::success(['locations'=>$locations]);
    }

    public function extraLocationsUpdatedOrder(Request $request){
        try{

            $validator=Validator::make($request->all(),[
                'extra_locations'=>'required|array',
                'extra_locations.*'=>'exists:locations,id',
                'image_id'=>'required|exists:media,id',
                'subscription_id'=>'required|exists:subscriptions,id',
                
            ]);
            if($validator->fails()){
                return Resp::error([$validator->errors()]);
            }

            $subscription_data=Subscription::find($request->input('subscription_id'));
            if(!$subscription_data){
                return Resp::error(['Subscription not found']);
            }

        $order=Orders::create([
            'escort_id'=>$subscription_data->escort_id,
            'plan_code'=>$subscription_data->plan_code,
            'start_date'=>$subscription_data->start_date,
            'end_date'=>$subscription_data->end_date,
            'payment_status'=>"PENDING",
            'image_id'=>$request->input('image_id'),
            'extra_location'=>$request->input('extra_locations'),
        ]);
           if(!$order){
                return Resp::error(['Failed to create order !']);
            }

            // Merge and deduplicate location arrays
        //$current_locations = is_array($subscription_data->extra_location) ? $subscription_data->extra_location : [];
        $new_locations = is_array($request->input('extra_locations')) ? $request->input('extra_locations') : [];
        //$merged_locations = collect(array_merge($current_locations, $new_locations))
        //    ->flatten()
        //    ->unique()
        //    ->values()
        //    ->toArray();

        $updated_subscription = $subscription_data->update([
            'extra_location' => $request->input('extra_locations'),
            'order_id' => $order->id,
            'image_id'=>$request->input('image_id')
        ]);
            if(!$updated_subscription){
                return Resp::error(['Failed to update subscription']);
            }
            return Resp::success(['order'=>$order]);
        }catch(\Exception $e){
            return Resp::error([$e->getMessage()]);
        }
    }

    public function createFreeSubscription(Request $request){
        $user=auth()->user();
        $order=Orders::create([
            'escort_id'=>$user->id,
            'plan_code'=>'P101',
            'start_date'=>date('Y-m-d'),
            'end_date'=>date('Y-m-d',strtotime('+10 days')),
            'payment_status'=>'PAID',
            'image_id'=>$request->input('image_id'),
            'extra_location'=>$request->input('extra_locations'),
        ]);
        $subscription=Subscription::create([
            'escort_id'=>$user->id,
            'plan_code'=>'P101',
            'status'=>'ACTIVE',
        ]);
    }


}


