<?php

namespace Modules\Fan\Http\Controllers;

use App\Services\Resp;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Auth\app\Http\Middleware\AuthMiddleware;
use Illuminate\Support\Facades\Log;
use Modules\Escort\app\Models\EscortSubscription;
use App\Models\BaseSubscription;
use App\Models\Location;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware(AuthMiddleware::class)->except('topLocation', 'getSubscriptions', 'locations');
    }
    public function locations(Request $request)
    {
        try {

            $locations = Location::query();


            if (!is_null($request->query('type'))) {
                $locations->where('type', $request->query('type'));
            }
            $result = $locations->get();
            return Resp::success(["list" => $result]);
        } catch (\Exception $e) {
            return Resp::error(['message' => 'Failed to fetch locations']);
        }
    }


    public function topLocation()
    {
        $result = EscortSubscription::join('profile', 'subscriptions.escort_id', '=', 'profile.escort_id')
            ->leftJoin('locations_cities', 'profile.city_id', '=', 'locations_cities.id')
            ->selectRaw('profile.city_id, COUNT(*) as subscription_count,locations_cities.name as city_name');
        $result = $result->groupBy('profile.city_id', 'locations_cities.name');
        return Resp::success(["list" => $result->get()]);
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

            if (!is_null($request->query('escort_id'))) {
                $subscriptions->where('escort_id', $request->query('escort_id'));
            }


            // Filter by ethnicity if provided
            if (!is_null($request->query('ethnicity'))) {
                $subscriptions->whereHas('escort.profile', function ($query) use ($request) {
                    $query->where('ethnicity', $request->query('ethnicity'));
                });
            }

            // Filter by cock_size if provided
            if (!is_null($request->query('cock_size'))) {
                $subscriptions->whereHas('escort.profile', function ($query) use ($request) {
                    $query->where('cock_size', $request->query('cock_size'));
                });
            }

            if (!is_null($request->query('orientation'))) {
                $subscriptions->whereHas('escort.profile', function ($query) use ($request) {
                    $query->where('orientation', $request->query('orientation'));
                });
            }

            if (!is_null($request->query('city_id'))) {
                $subscriptions->whereHas('escort.profile', function ($query) use ($request) {
                    $query->where('city_id', $request->query('city_id'));
                });
            }


            if (!is_null($request->query('region_id'))) {
                $subscriptions->whereHas('escort.profile', function ($query) use ($request) {
                    $query->where('region_id', $request->query('region_id'));
                });
            }
            if (!is_null($request->query('county_id'))) {
                $subscriptions->whereHas('escort.profile', function ($query) use ($request) {
                    $query->where('county_id', $request->query('county_id'));
                });
            }

            // Retrieve subscriptions with related escort and profile
            $result = $subscriptions->with('escort', 'escort.profile.county', 'escort.profile.region', 'escort.profile.city')->get();
            return Resp::success(["list" => $result]);
        } catch (\Exception $e) {
          
        }

    }
}
