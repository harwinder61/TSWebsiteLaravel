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
use App\Models\BaseReviews;
use PhpParser\Node\Stmt\Switch_;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware(AuthMiddleware::class)->except('topLocation', 'getSubscriptions', 'locations')->except('topLocation','getSubscriptions','locations');
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
            ->leftJoin('locations', 'profile.city_id', '=', 'locations.id')
            ->selectRaw('locations.id, COUNT(*) as subscription_count,locations.name as city_name,locations.slug as slug');
        
        $result = $result->groupBy('locations.id', 'locations.name','locations.slug');
        return Resp::success(["list" => $result->get()]);
    }

    public function getSubscriptions(Request $request)
    {

        try {

            $user = auth()->user();
            
            

            // $subscriptions = EscortSubscription::where('escort_id', $user->id);

            $subscriptions = EscortSubscription::query();
            // Add join with plans table
            $subscriptions->leftJoin('plans', 'subscriptions.plan_code', '=', 'plans.code')
            ->select('subscriptions.*', 'plans.title as plan_title'); // Add plans.title to the selection



            if ($request->query('slug')) {

                $slug = $request->query('slug');

                $location=Location::where('slug','like','%'.$slug.'%')->first();
                $type=$location->type;
                Log::info($type);
                switch($type){
                    case 'city':
                        $request->merge(['city_id' => $location->id]);
                        break;
                    case 'county':
                        $request->merge(['county_id' => $location->id]);
                        break;
                    case 'region':
                        $request->merge(['region_id' => $location->id]);
                        break;

                }
                }
                if (!is_null($request->query('county_slug'))) {
                    
                    $countySlug = $request->query('county_slug');
                    $subscriptions->whereHas('escort.profile', function ($query) use ($countySlug) {
                        $query->whereHas('county', function($q) use ($countySlug) {
                            $q->where('slug','like','%'.$countySlug.'%');
                        });
                    });
                    }
                    if (!is_null($request->query('region_slug'))) {
                        $regionSlug = $request->query('region_slug');
                        $subscriptions->whereHas('escort.profile', function ($query) use ($regionSlug) {
                            $query->whereHas('region', function($q) use ($regionSlug) {
                                $q->where('slug','like','%'.$regionSlug.'%');
                            });
                        });
                        }
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
            if(!is_null($request->query('status'))){
                if($request->query('status')=='active'){
                    $subscriptions->where('end_date','>',now());
                }elseif($request->query('status')=='expired'){
                    $subscriptions->where('end_date','<',now());
                }
                

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

            if (!is_null($request->query('name'))) {
                $subscriptions->whereHas('escort.profile', function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->query('name') . '%');
                });
            }

            if (!is_null($request->query('username'))) {
                $subscriptions->whereHas('escort.profile', function ($query) use ($request) {
                    $query->where('username', 'like', '%' . $request->query('username') . '%');
                });
            }



            
            $perPage = $request->query('per_page', 10); // Default items per page
            $page = $request->query('page', 1); // Default to first page
            $offset = ($page - 1) * $perPage;
    
            // Get total count for pagination info
            $totalCount = $subscriptions->count();
            
    
            // Fetch the results with offset and limit
            $result = $subscriptions->with('escort', 'escort.profile.county','escort.profile.city','escort.profile.region','escort.profile.reviews')
                ->offset($offset)
                ->limit($perPage)
                ->get();

                // Calculate average rating for the specific escort_id
                //$averageRating = BaseReviews::whereIn('escort_id', $result->pluck('escort_id')->toArray())
                //    ->selectRaw('AVG(photo_accuracy) as avg_photo_accuracy, AVG(service) as avg_service, AVG(clean_liness) as avg_clean_liness, AVG(location) as avg_location, AVG(value_for_money) as avg_value_for_money')
                //    ->get();
    
                // Fetch reviews
                //$reviews = BaseReviews::whereIn('escort_id', $result->pluck('escort_id')->toArray())->get();
                
     

                //$reviews = BaseReviews::whereIn('escort_id',$result->pluck('escort_id')->toArray())->get();
                foreach ($result as $subscription) {
                    $escort = $subscription->escort;
                    $reviews = $escort->profile->reviews;
                
                    // Initialize variables to accumulate the sum of all fields
                    $totalPhotoAccuracy = 0;
                    $totalService = 0;
                    $totalCleanliness = 0;
                    $totalLocation = 0;
                    $totalValueForMoney = 0;
                    $totalReviews = count($reviews);
                
                    // If there are reviews, calculate the sum for each field
                    if ($totalReviews > 0) {
                        foreach ($reviews as $review) {
                            $totalPhotoAccuracy += $review->photo_accuracy;
                            $totalService += $review->service;
                            $totalCleanliness += $review->clean_liness;
                            $totalLocation += $review->location;
                            $totalValueForMoney += $review->value_for_money;
                        }
                
                        // Now, calculate the average of all fields
                        $averageRating = (
                            $totalPhotoAccuracy + 
                            $totalService + 
                            $totalCleanliness + 
                            $totalLocation + 
                            $totalValueForMoney
                        ) / (5 * $totalReviews); // Divide by 5 (fields) and number of reviews
                
                        // Optionally, store the calculated average rating to the profile
                        $escort->profile->avg_rating = round($averageRating, 2); // Round to 2 decimal places
                
                        // Save the average rating to the database (if needed)
                        // $escort->profile->save();
                    }
                }

            
                return Resp::success(["list" => $result,'pagination'=>['total_results'=>$totalCount,'total_pages'=>ceil($totalCount/$perPage),'page_number'=>$page,'page_size'=>$perPage]]);

            // Retrieve subscriptions with related escort and profile
        } catch (\Exception $e) {
            
            return Resp::error(['message' => 'Something went wrong'.$e->getMessage()]);
        }

    }
}
