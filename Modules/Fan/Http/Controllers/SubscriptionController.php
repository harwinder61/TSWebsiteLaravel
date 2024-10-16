<?php

namespace Modules\Fan\Http\Controllers;

use App\Services\Resp;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Auth\app\Http\Middleware\AuthMiddleware;
use Illuminate\Support\Facades\Log;
use Modules\Escort\app\Models\EscortSubscription;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware(AuthMiddleware::class);
    }
    
    public function getSubscriptions(Request $request)
    {
        try {
            
            $user = auth()->user();
            // $subscriptions = EscortSubscription::where('escort_id', $user->id);
            $subscriptions = EscortSubscription::query();

            // Filter by plan_code if provided
            if (!is_null($request->query('plan_code'))) {
                $subscriptions->where('plan_code', $request->query('plan_code'));
            }

            if(!is_null($request->query('escort_id'))){
                $subscriptions->where('escort_id', $request->query('escort_id'));
            }


            // Filter by ethnicity if provided
            if (!is_null($request->query('ethnicity'))) {
                $subscriptions->whereHas('escort.profile', function($query) use ($request) {
                    $query->where('ethnicity', $request->query('ethnicity'));
                });
            }
            
            // Filter by cock_size if provided
            if (!is_null($request->query('cock_size'))) {
                $subscriptions->whereHas('escort.profile', function($query) use ($request) {
                    $query->where('cock_size', $request->query('cock_size'));
                });
            }

            // Retrieve subscriptions with related escort and profile
            $result = $subscriptions->with('escort', 'escort.profile')->get();
            return Resp::success(["list" => $result]);

        } catch (\Exception $e) {
            Log::error('Error fetching subscriptions: ' . $e->getMessage());
        }


        if (!is_null($request->query('orientation'))) {
            $subscriptions->whereHas('escort.profile', function($query) use ($request) {
                $query->where('orientation', $request->query('orientation'));
            });
        }
   
          if(!is_null($request->query('city_id'))){
            $subscriptions->whereHas('escort.profile', function($query) use ($request) {
                $query->where('city_id', $request->query('city_id'));
            });
          }
          
          if(!is_null($request->query('region_id'))){
            $subscriptions->whereHas('escort.profile', function($query) use ($request) {
                $query->where('region_id', $request->query('region_id'));
            });
          }


        // Retrieve subscriptions with related escort and profile
        $result = $subscriptions->with('escort', 'escort.profile')->get();
        return Resp::success(["list" => $result]);

    }
}
