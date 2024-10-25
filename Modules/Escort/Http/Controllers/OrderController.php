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







class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware(AuthEscort::class)->except('paymentSuccess','paymentCancel');
    }

    function createOrder(Request $request){
        $user=auth()->user();
        $validator=Validator::make($request->all(),[
            'plan_code'=>'required|string|exists:plans,code',
            'start_date'=>'required|date',
            'payment_status'=>'required|string|in:PENDING,PAID',
        ]);
        if($validator->fails()){
            return Resp::error([$validator->errors()]);
        }
        $plan=Plan::where('code',$request->input('plan_code'))->first();
        if(!$plan){
            return Resp::error(['Plan not found']);
        }
        $days=$plan->days;

        $end_date=date('Y-m-d',strtotime($request->input('start_date')." + $days days"));

        $subscription_count=Subscription::where('plan_code',$request->input('plan_code'))
                ->where('status','ACTIVE')
                ->get()->count();

       
        $fiveMinutesAgo = now()->subMinutes(5)->toDateTimeString();
        $pendingOrders = Orders::where('payment_status', 'PENDING')
            ->where('created_at', '>=', $fiveMinutesAgo)
            ->get();

        $pending_orders_count=$pendingOrders->count();


        $max_users=Plan::where('code',$request->input('plan_code'))
                        ->first('allowed_user_account');
        $max_users=$max_users->allowed_user_account;
        
        $total_orders_count=$subscription_count+$pending_orders_count;

        if($total_orders_count>=$max_users){
            return Resp::error(['Max subscription reached plan not available']);
        }
        $start_date2=Carbon::parse($request->input('start_date'));
        $end_date2=Carbon::parse($end_date);

        $weekly_sub_exists=null;
        if($request->input('plan_code')=="P101"){
        
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
        

            if($weekly_sub_exists->isNotEmpty()){
                return Resp::error(['Weekly subscription is already owned by someone']);
            }
    
        }
        

        $order=Orders::create([
            'escort_id'=>$user->id,
            'plan_code'=>$request->input('plan_code'),
            'start_date'=>$request->input('start_date'),
            'end_date'=>$end_date,
            'payment_status' => 'PENDING',
        ]);
        if(!$order){
            return Resp::error(['Failed to create order']);
        }
        $session_url="";
        try{
            // Set the Stripe secret key
            Stripe::setApiKey(env('STRIPE_SECRET'));

            $plan=Plan::where('code',$request->input('plan_code'))->first();
            $amount=intval($plan->price)*100;
            $title=$plan->title;
            
        $paymentIntent = PaymentIntent::create([
            'amount' => $amount, // amount in cents
            'currency' => 'eur',
            'metadata' => ['order_id' => $order->id],
        ]);

        //$session_url=$session->url;
        }catch(\Exception $e){
            return Resp::error([$e->getMessage()]);
        }
        return Resp::success(['client_secret'=>$paymentIntent->client_secret,'dpmCheckerLink' => "https://dashboard.stripe.com/settings/payment_methods/review?transaction_id={$paymentIntent->id}",
   ]);
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


