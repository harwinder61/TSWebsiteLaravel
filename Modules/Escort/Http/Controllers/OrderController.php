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






class OrderController extends Controller
{
    public function __construct()
    {

    }


    public function createOrder(Request $request){
        $user = auth()->user();
        
        $validator = Validator::make($request->all(), [
            'plan_code' => 'required|string|exists:plans,code',
            'start_date' => 'required|date',
            'payment_status' => 'required|string|in:PENDING,PAID',
            'only_fans_link' => 'nullable|string',
            'many_vids_link' => 'nullable|string',
            'fan_centro_link' => 'nullable|string',
            'image_id' => 'required|exists:media,id',
        ]);
    
        if ($validator->fails()) {
            return Resp::fieldErrors(['field_errors' => $validator->errors()]);
        }
    
        $plan = Plan::where('code', $request->input('plan_code'))->first();
        if (!$plan) {
            return Resp::error(['Plan not found']);
        }
    
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
            return Resp::error(['Max subscription reached, plan not available']);
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
                return Resp::error(['Weekly subscription is already owned by someone']);
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
        ]);
        
        if (!$order) {
            return Resp::error(['Failed to create order']);
        }
    
        // Stripe payment logic remains the same
        $session_url = "";
        try {
            // Set the Stripe secret key
            Stripe::setVerifySslCerts(false);
            Stripe::setApiKey(env('STRIPE_SECRET'));
    
            $plan = Plan::where('code', $request->input('plan_code'))->first();
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
            'order_id' => $order->id,
            'client_secret' => $paymentIntent->client_secret,
            'dpmCheckerLink' => "https://dashboard.stripe.com/settings/payment_methods/review?transaction_id={$paymentIntent->id}",
        ];
    
// ... existing code ...

$media = Media::where('id', $request->input('image_id'))
    ->where('escort_id', $user->id)
    ->first();

if (!$media || $media->id != $request->input('image_id')) {
    return Resp::error(['message' => 'Invalid image id']);
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
    
        return Resp::success($response);
    }
    
    
    function webhook_payment_status_update(Request $request){
        
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
            

            $subscription_exists=Subscription::where('escort_id',$order->escort_id)
                ->where('plan_code',$order->plan_code)
                ->where('status','ACTIVE')
                ->first();
                

            if($subscription_exists){
                return Resp::error(['Subscription already exists']);
            }
            
            $subscription=Subscription::create([
                'escort_id'=>$order->escort_id,
                'order_id'=>$order->id,
                'plan_code'=>$order->plan_code,
                'start_date'=>$order->start_date,
                'image_id'=>$order->image_id,
                'status'=>'ACTIVE',
                'end_date'=>date('Y-m-d',strtotime($order->start_date." + $days days")),
            ]);
            if(!$subscription){
                return Resp::error(['Failed to create subscription']);
            }
        }else{
          return Resp::error(['Order already paid']);
        }
        return Resp::success([$order]);
    }

    function getSubscription() {
        $user = auth()->user();
        
        $subscription = Subscription::where('escort_id', $user->id)
            ->where('status', 'ACTIVE')
            ->get(); 
        return Resp::success(["list" => $subscription]);
    }  

    

}


