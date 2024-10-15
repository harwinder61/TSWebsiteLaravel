<?php

namespace Modules\Escort\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use App\Services\Resp;
use Modules\Auth\Entities\User;
use Modules\Escort\app\Http\Middleware\AuthEscort;
use Modules\Escort\app\Models\Orders;
use Modules\Escort\app\Models\Subscription;
use Modules\Plans\app\Models\Plans;
use Illuminate\Support\Facades\Log;



class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware(AuthEscort::class);
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
        $order=Orders::create([
            'escort_id'=>$user->id,
            'plan_code'=>$request->input('plan_code'),
            'start_date'=>$request->input('start_date'),
            'payment_status' => 'PENDING',
//            'payment_status'=>$request->input('payment_status'),
        ]);
        if(!$order){
            return Resp::error(['Failed to create order']);
        }
        return Resp::success([$order]);
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
            $plan=Plans::where('code',$order->plan_code)->first();
            if(!$plan){
                return Resp::error(['Plan not found']);
            }
            $days=$plan->days;
            $subscription_exists=Subscription::where('escort_id',$order->escort_id)
                ->where('plan_code',$order->plan_code)
                ->where('status','ACTIVE')
                ->first();
                Log::info("Subscription query parameters:", [
                    'escort_id' => $order->escort_id,
                    'plan_code' => $order->plan_code,
                    'status' => 'ACTIVE'
                ]);
            Log::info("Subscription exists :");
            Log::info($subscription_exists);
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


