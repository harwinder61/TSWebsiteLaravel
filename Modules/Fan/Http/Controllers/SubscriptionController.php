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
use Modules\Fan\app\Models\FanReviews;
use App\Models\Reviews;

    
class SubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware(AuthMiddleware::class)->except([
            'topLocation',
            'getSubscriptions',
            'locations',
            'slugToLocation',
            'listReviews',
            'getEscortFanlist'
        ]);
    }

    public function getAllListReviews(Request $request)
    {

        $statuses = $request->query('status');
        $filter = $request->query('filter');
    
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);
        $offset = ($page - 1) * $perPage;
    
        $reviews = BaseReviews::with('user') // Add relationship with user
            ->orderBy('created_at', 'desc') // Order by created_at in descending order
            ->offset($offset)
            ->limit($perPage)
            ->get();
    
        if ($statuses) {
            $statuses = explode(',', $statuses); // Convert comma-separated string to array
            $reviews = $reviews->whereIn('status', $statuses);
        }
    
        if ($filter === '0') {
            $reviews = $reviews->where('avg_rating', '<', 3); // Show only reviews with avg rating < 3
        } elseif ($filter === '1') {
            $reviews = $reviews->where('avg_rating', '>=', 3); // Show only reviews with avg rating >= 3
        }
    
        $reviews = $reviews->map(function ($review) {
            $review->avg_rating = ($review->photo_accuracy + $review->service + $review->clean_liness + $review->location + $review->value_for_money) / 5;
            return $review;
        });
    
        $totalResults = BaseReviews::count();
        $totalPages = ceil($totalResults / $perPage);
        $totalRatings = $reviews->sum('avg_rating');
        $averageRating = $totalRatings / $reviews->count();
    
        $pagination = [
            'total_results' => $totalResults,
            'total_pages' => $totalPages,
            'page' => (int)$page,
            'page_size' => $perPage,
            'average_rating' => $averageRating,
        ];
    
        return Resp::success(['reviews' => $reviews->values(), 'pagination' => $pagination]);
    }
    
    


public function listReviews($id, Request $request)
{
    $query = FanReviews::join('profile', 'reviews.escort_id', '=', 'profile.escort_id')
        ->leftJoin('users', 'reviews.user_id', '=', 'users.id')
        ->select('reviews.*', 'profile.name as escort_name', 'users.username as fan_name')
        ->without('reviews.fan')
        ->with(['escort.profile.media']);

    $query->where(function ($query) use ($id) {
        $query->where('reviews.escort_id', $id)
            ->orWhere('reviews.user_id', $id);
    });

    $escortId = $request->query('escort_id');
    if ($escortId) {
        $query->where('reviews.escort_id', $escortId);
    }

    $reviews = $query->paginate($request->input('page_size', 4));

    $total_reviews = $reviews->total();
    $total_overall_average = 0;
    $total_overall_photo_accuracy = 0;
    $total_overall_service = 0;
    $total_overall_cleanliness = 0;
    $total_overall_location = 0;
    $total_overall_value_for_money = 0;
    $sum_of_single_review_avg = 0;
    $sum_of_single_photo_accuracy = 0;
    $sum_of_single_service = 0;
    $sum_of_single_cleanliness = 0;
    $sum_of_single_location = 0;
    $sum_of_single_value_for_money = 0;

    foreach ($reviews as $review) {
        $averageRating = (
            $review->photo_accuracy +
            $review->service +
            $review->clean_liness +
            $review->location +
            $review->value_for_money
        ) / 5;
        $sum_of_single_photo_accuracy += $review->photo_accuracy;
        $sum_of_single_service += $review->service;
        $sum_of_single_cleanliness += $review->clean_liness;
        $sum_of_single_location += $review->location;
        $sum_of_single_value_for_money += $review->value_for_money;

        $review->avg_rating = round($averageRating, 2);
        $sum_of_single_review_avg = $sum_of_single_review_avg + round($averageRating, 2);
        $total_overall_average += $averageRating;
        $total_overall_photo_accuracy += $review->photo_accuracy;
        $total_overall_service += $review->service;
        $total_overall_cleanliness += $review->clean_liness;
        $total_overall_location += $review->location;
        $total_overall_value_for_money += $review->value_for_money;
    }

    if ($total_reviews > 0) {
        $total_overall_average = $total_overall_average / $total_reviews;
        $total_overall_photo_accuracy = $total_overall_photo_accuracy / $total_reviews;
        $total_overall_service = $total_overall_service / $total_reviews;
        $total_overall_cleanliness = $total_overall_cleanliness / $total_reviews;
        $total_overall_location = $total_overall_location / $total_reviews;
        $total_overall_value_for_money = $total_overall_value_for_money / $total_reviews;
    }

    return Resp::success([
        'list' => $reviews,
        'total' => $total_reviews,
        'sum_of_single_review_avg' => $total_overall_average,
        'total_overall_photo_accuracy' => $total_overall_photo_accuracy,
        'total_overall_service' => $total_overall_service,
        'total_overall_cleanliness' => $total_overall_cleanliness,
        'total_overall_location' => $total_overall_location,
        'total_overall_value_for_money' => $total_overall_value_for_money
    ]);
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
            ->selectRaw('locations.id, COUNT(*) as subscription_count,locations.name as city_name,locations.type as location_type,locations.slug as slug');
        
        $result = $result->groupBy('locations.id', 'locations.name','locations.slug','locations.type');
        return Resp::success(["list" => $result->get()]);
    }

    public function getSubscriptions(Request $request)
    {
        try {
            $user = auth()->user();
            $locationType="";
            $subscriptions = EscortSubscription::query();
            
            $subscriptions->leftJoin('plans', 'subscriptions.plan_code', '=', 'plans.code')
                ->select('subscriptions.*', 'plans.title as plan_title')
                ->where('subscriptions.end_date','>',now());
            if ($request->query('slug')) {
                $slug = $request->query('slug');

                $location=Location::where('slug','like','%'.$slug.'%')->first();
                $type=$location->type;
                switch($type){
                    case 'city':
                        $locationType='city';
                        $request->merge(['city_id' => $location->id]);
                        break;
                    case 'county':
                        $locationType='county';
                        $request->merge(['county_id' => $location->id]);
                        break;
                    case 'region':
                        $locationType='region';
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

            if (!is_null($request->query('plan_code'))) {
                $subscriptions->where('plan_code', $request->query('plan_code'));
            }

            if (!is_null($request->query('escort_id'))) {
                $subscriptions->where('escort_id', $request->query('escort_id'));
            }

            if (!is_null($request->query('ethnicity'))) {
                $subscriptions->whereHas('escort.profile', function ($query) use ($request) {
                    $query->where('ethnicity', $request->query('ethnicity'));
                });
            }

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
                

                $subscriptions->where(function ($query) use ($request) {
                    $query->whereHas('escort.profile', function ($query) use ($request) {
                        $query->where('city_id', $request->query('city_id'));
                    })
                    ->orWhere(function ($query) use ($request) {
                        // Check if the city_id exists in the extra_location JSON column
                        $query->whereJsonContains('extra_location', $request->query('city_id'));
                    });
                });
                

            }
    
            if (!is_null($request->query('region_id'))) {

                $subscriptions->whereHas('escort.profile', function ($query) use ($request) {
                    $query->where('region_id', $request->query('region_id'));
                })
                ->orWhere(function ($query) use ($request) {
                    // Check if the city_id exists in the extra_location JSON column
                    $query->whereJsonContains('extra_location', $request->query('region_id'));
                });
            }

            if (!is_null($request->query('county_id'))) {

                $subscriptions->whereHas('escort.profile', function ($query) use ($request) {
                    $query->where('county_id', $request->query('county_id'));
                })
                ->orWhere(function ($query) use ($request) {
                    // Check if the city_id exists in the extra_location JSON column
                    $query->whereJsonContains('extra_location', $request->query('county_id'));
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
            
            $perPage = $request->query('per_page',50); 
            $page = $request->query('page', 1);
            $offset = ($page - 1) * $perPage;
    
            $totalCount = $subscriptions->count();

            $result = $subscriptions->with([
                'escort',
                'escort.profile.county',
                'escort.profile.city',
                'escort.profile.region',
                'escort.profile.reviews',
                'escort.profile.media' ,
                'escort.profile.rates',
                'orders'
            ])
                ->offset($offset)
                ->limit($perPage)
                ->get();


            foreach ($result as $subscription) {
                $escort = $subscription->escort;
                $reviews = $escort->profile->reviews ?? [];
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
                
                      
                        $averageRating = (
                            $totalPhotoAccuracy + 
                            $totalService + 
                            $totalCleanliness + 
                            $totalLocation + 
                            $totalValueForMoney
                        ) / (5 * $totalReviews); 
                
                        
                        $escort->profile->avg_rating = round($averageRating, 2); 
                
              
                    }
                }


            
                return Resp::success(["list" => $result,'location_type'=>$locationType,'pagination'=>['total_results'=>$totalCount,'total_pages'=>ceil($totalCount/$perPage),'page_number'=>$page,'page_size'=>$perPage]]);

            // Retrieve subscriptions with related escort and profile
        } catch (\Exception $e) {
            return Resp::error(['message' => 'Something went wrong'.$e->getMessage()]);
        }
    }

    public function getAdvertLists(Request $request)
    {
        try {
            $user = auth()->user();
            
            $locationType="";
            $subscriptions = EscortSubscription::query();
            
            $subscriptions->leftJoin('plans', 'subscriptions.plan_code', '=', 'plans.code')
                ->select('subscriptions.*', 'plans.title as plan_title');

            if ($request->query('slug')) {
                $slug = $request->query('slug');

                $location=Location::where('slug','like','%'.$slug.'%')->first();
                $type=$location->type;
                switch($type){
                    case 'city':
                        $locationType='city';
                        $request->merge(['city_id' => $location->id]);
                        break;
                    case 'county':
                        $locationType='county';
                        $request->merge(['county_id' => $location->id]);
                        break;
                    case 'region':
                        $locationType='region';
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

            if (!is_null($request->query('plan_code'))) {
                $subscriptions->where('plan_code', $request->query('plan_code'));
            }

            if (!is_null($request->query('escort_id'))) {
                $subscriptions->where('escort_id', $request->query('escort_id'));
            }

            if (!is_null($request->query('ethnicity'))) {
                $subscriptions->whereHas('escort.profile', function ($query) use ($request) {
                    $query->where('ethnicity', $request->query('ethnicity'));
                });
            }

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
            
            $perPage = (int)$request->query('per_page',10); 
            $page = (int)$request->query('page', 1);
            $offset = ($page - 1) * $perPage;
    
            $totalCount = $subscriptions->count();

            $result = $subscriptions->with([
                'escort',
                'escort.profile.county',
                'escort.profile.city',
                'escort.profile.region',
                'escort.profile.reviews',
                'escort.profile.media' ,
                'escort.profile.rates',
                'orders'
            ])
                ->offset($offset)
                ->limit($perPage)
                ->get();

            foreach ($result as $subscription) {
                $escort = $subscription->escort;
                $reviews = $escort->profile->reviews ?? [];
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
                
                      
                        $averageRating = (
                            $totalPhotoAccuracy + 
                            $totalService + 
                            $totalCleanliness + 
                            $totalLocation + 
                            $totalValueForMoney
                        ) / (5 * $totalReviews); 
                
                        
                        $escort->profile->avg_rating = round($averageRating, 2); 
                
              
                    }
                }


            
                return Resp::success(["list" => $result,'location_type'=>$locationType,'pagination'=>['total_results'=>$totalCount,'total_pages'=>ceil($totalCount/$perPage),'page_number'=>$page,'page_size'=>$perPage,'page'=>(int)$page]]);

            // Retrieve subscriptions with related escort and profile
        } catch (\Exception $e) {
            return Resp::error(['message' => 'Something went wrong'.$e->getMessage()]);
        }
    }

    public function slugToLocation(Request $request){
        $slug=$request->input('slug');
        $location = Location::where('slug', $slug)->first();
        
        if ($location) {
            if ($location->type == 'city') {
                $county = Location::where('id', $location->parent_id)->first();
                $region = Location::where('id', $county->parent_id)->first();
                $city_data = EscortSubscription::join('profile', 'subscriptions.escort_id', '=', 'profile.escort_id')
                ->leftJoin('locations', 'profile.city_id', '=', 'locations.id')
                ->where('profile.city_id',$location->id)
                ->selectRaw('COUNT(*) as subscription_count,locations.name as location_name')
                ->groupBy('profile.city_id','locations.name')
                ->first();

                $city_data = EscortSubscription::join('profile', 'subscriptions.escort_id', '=', 'profile.escort_id')
                ->where('profile.city_id',$location->id)
                ->selectRaw('COUNT(*) as subscription_count')
                ->first();


                $county_data = EscortSubscription::join('profile', 'subscriptions.escort_id', '=', 'profile.escort_id')
                ->where('profile.county_id',$county->id)
                ->selectRaw('COUNT(*) as subscription_count')
                ->first();


                $region_data = EscortSubscription::join('profile', 'subscriptions.escort_id', '=', 'profile.escort_id')
                ->where('profile.region_id',$region->id)
                ->selectRaw('COUNT(*) as subscription_count')
                ->first();

                $location['subscription_count']=$city_data->subscription_count;
                $county['subscription_count']=$county_data->subscription_count;
                $region['subscription_count']=$region_data->subscription_count;
                return Resp::success(['city'=>$city_data ,'county'=> $county_data,'location_type'=>$location->type,'data'=>['county'=>$county,'region'=>$region,'city'=>$location]]);
            } elseif ($location->type == 'county') {
                $region = Location::where('id', $location->parent_id)->first();

                $county_data = EscortSubscription::join('profile', 'subscriptions.escort_id', '=', 'profile.escort_id')
                ->where('profile.county_id',$location->id)
                ->selectRaw('COUNT(*) as subscription_count')
                ->first();


                $region_data = EscortSubscription::join('profile', 'subscriptions.escort_id', '=', 'profile.escort_id')
                ->where('profile.region_id',$region->id)
                ->selectRaw('COUNT(*) as subscription_count')
                ->first();

                $location['subscription_count']=$county_data->subscription_count;
                $region['subscription_count']=$region_data->subscription_count;

                return Resp::success(['location_type'=>$location->type,'data'=>['county'=>$location,'region'=>$region]]);
                
            } else {
                $region = $location;
            }
        }


        $region_data = EscortSubscription::join('profile', 'subscriptions.escort_id', '=', 'profile.escort_id')
                ->where('profile.region_id',$location->id)
                ->selectRaw('COUNT(*) as subscription_count')
                ->first();
        $region['subscription_count']=$region_data->subscription_count;

        return Resp::success(['location_type'=>$location->type, 'data'=>['region'=>$region]]);
    }

}
