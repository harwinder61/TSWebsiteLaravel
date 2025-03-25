<?php
/////arv///////
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
            'getEscortFanlist',
            'getTSWeekSubscriptions'
        ]);
    }



    // public function getAllListReviews(Request $request)
    // {
    //     $statuses = $request->query('status');
    //     $filter = $request->query('filter');
    //     $s = $request->query('s');
    //     $perPage = $request->query('per_page', 10);
    //     $page = $request->query('page', 1);
    //     $offset = ($page - 1) * $perPage;
    //     $escortId = $request->query('escort_id');
    //     $fanId = $request->query('fan_id');
    //     $verifiedStatus = $request->query('verified_status'); // New filter parameter

    //     // Start building the query for reviews
    //     $reviewsQuery = BaseReviews::with('user', 'escort', 'fan') // Relationship with user and escort
    //         ->orderBy('created_at', 'desc') // Order by created_at descending
    //         ->offset($offset)
    //         ->limit($perPage);

    //     // Apply filters based on query parameters
    //     if ($statuses) {
    //         $statuses = explode(',', $statuses); // Convert comma-separated string to array
    //         $reviewsQuery->whereIn('status', $statuses);
    //     }

    //     if ($s) {
    //         $reviewsQuery->whereHas('user', function ($query) use ($s) {
    //             $query->where('username', 'like', '%' . $s . '%');
    //         });
    //     }

    //     if ($filter === '0') {
    //         $reviewsQuery->where('avg_rating', '<', 3); // avg_rating < 3
    //     } elseif ($filter === '1') {
    //         $reviewsQuery->where('avg_rating', '>=', 3); // avg_rating >= 3
    //     }

    //     if ($escortId) {
    //         $reviewsQuery->where('escort_id', $escortId); // Filter by escort ID
    //     }

    //     if ($fanId) {
    //         $reviewsQuery->where('user_id', $fanId); // Filter by fan ID
    //     }

    //     // Apply the new verified_status filter if provided
    //     if ($verifiedStatus !== null) {
    //         $reviewsQuery->where('verified_status', $verifiedStatus); // Filter by verified_status
    //     }

    //     // Get the filtered reviews
    //     $reviews = $reviewsQuery->get()->map(function ($review) {
    //         $review->avg_rating = ($review->photo_accuracy + $review->service + $review->clean_liness + $review->location + $review->value_for_money) / 5;
    //         return $review;
    //     });

    //     // Get the total count of filtered reviews for pagination
    //     $totalResults = $reviewsQuery->count();
    //     $totalPages = ceil($totalResults / $perPage);

    //     // Calculate the total average rating of the reviews
    //     $totalRatings = $reviews->sum('avg_rating');
    //     $averageRating = $reviews->count() > 0 ? $totalRatings / $reviews->count() : 0;

    //     // Pagination response
    //     $pagination = [
    //         'total_results' => $totalResults,
    //         'total_pages' => $totalPages,
    //         'page' => (int)$page,
    //         'page_size' => $perPage,
    //         'average_rating' => $averageRating,
    //     ];
    //     // Return response with reviews and pagination data
    //     return Resp::success(['reviews' => $reviews->values(), 'pagination' => $pagination]);
    // }


    public function getAllListReviews(Request $request)
    {
        // Get request parameters
        $statuses = $request->query('status');
        $filter = $request->query('filter');
        $s = $request->query('s');
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);
        $offset = ($page - 1) * $perPage;
        $escortId = $request->query('escort_id');
        $fanId = $request->query('fan_id');
        $verifiedStatus = $request->query('verified_status'); // New filter parameter

        // Start building the query for reviews
        $reviewsQuery = BaseReviews::with('user', 'escort', 'fan') // Relationship with user and escort
            ->orderBy('created_at', 'desc'); // Order by created_at descending

        // Apply filters based on query parameters

        // Check for status=0 before applying other filters
        if ($statuses === '0') {
            $reviewsQuery->where('status', 0); // Filter reviews where status is 0
        } elseif ($statuses) {
            $statuses = explode(',', $statuses); // Convert comma-separated string to array
            $reviewsQuery->whereIn('status', $statuses);
        }

        if ($s) {
            $reviewsQuery->whereHas('user', function ($query) use ($s) {
                $query->where('username', 'like', '%' . $s . '%');
            });
        }

        if ($filter === '0') {
            $reviewsQuery->where('avg_rating', '<', 3); // avg_rating < 3
        } elseif ($filter === '1') {
            $reviewsQuery->where('avg_rating', '>=', 3); // avg_rating >= 3
        }

        if ($escortId) {
            $reviewsQuery->where('escort_id', $escortId); // Filter by escort ID
        }

        if ($fanId) {
            $reviewsQuery->where('user_id', $fanId); // Filter by fan ID
        }

        // Apply the new verified_status filter if provided
        if ($verifiedStatus !== null) {
            $reviewsQuery->where('verified_status', $verifiedStatus); // Filter by verified_status
        }

        // DEBUG: Log the full SQL query before applying pagination
        \Log::info('SQL Query before pagination: ' . $reviewsQuery->toSql());

        // 1. Get the total count of filtered reviews (without pagination)
        $totalResults = $reviewsQuery->count();

        // DEBUG: Log the total count
        \Log::info('Total filtered reviews count: ' . $totalResults);

        // 2. Apply pagination (skip and take)
        $reviews = $reviewsQuery->skip($offset)->take($perPage)->get()->map(function ($review) {
            $review->avg_rating = ($review->photo_accuracy + $review->service + $review->clean_liness + $review->location + $review->value_for_money) / 5;
            return $review;
        });

        // DEBUG: Log the number of reviews retrieved for the current page
        \Log::info('Reviews count on page ' . $page . ': ' . $reviews->count());

        // 3. Calculate the total pages based on the total count
        $totalPages = ceil($totalResults / $perPage);

        // Calculate the total average rating of the reviews
        $totalRatings = $reviews->sum('avg_rating');
        $averageRating = $reviews->count() > 0 ? $totalRatings / $reviews->count() : 0;

        // Pagination response
        $pagination = [
            'total_results' => $totalResults,
            'total_pages' => $totalPages,
            'page' => (int) $page,
            'page_size' => $perPage,
            'average_rating' => $averageRating,
        ];

        // Return response with reviews and pagination data
        return Resp::success(['reviews' => $reviews->values(), 'pagination' => $pagination]);
    }






    public function listReviews($id, Request $request)
    {
        $query = FanReviews::join('profile', 'reviews.escort_id', '=', 'profile.escort_id')
            ->leftJoin('users', 'reviews.user_id', '=', 'users.id')
            ->select('reviews.*', 'profile.name as escort_name', 'users.username as fan_name')
            ->without('reviews.fan')
            ->with(['escort.profile.media']);
            
        
        if($request->query('status')!=null){
            $status = $request->query('status');
            $query->where('reviews.status', strval($status));
        }

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


    // public function topLocation(Request $request)
    // {
    //     $result = EscortSubscription::join('profile', 'subscriptions.escort_id', '=', 'profile.escort_id')
    //         ->where('subscriptions.end_date', '>', now())
    //         ->where('subscriptions.is_hidden', 0);

    //     $byPlanOrder = filter_var($request->query('byPlanOrder'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    //     if ($byPlanOrder) {
    //         $rawSubQuary = '(SELECT escort_id, MAX(end_date) as latest_end_date, MAX(id)
    //         as max_id FROM subscriptions GROUP BY escort_id) as latest_subscription';
    //     } else {
    //         // $rawSubQuary = '(SELECT escort_id, MAX(end_date) as latest_end_date, MAX(id)
    //         // as max_id FROM subscriptions GROUP BY escort_id, plan_code) as latest_subscription';

    //         $rawSubQuary = '(
    //             SELECT t.escort_id, t.latest_end_date, t.max_id
    //             FROM (
    //                 SELECT escort_id, end_date as latest_end_date, id as max_id,
    //                        ROW_NUMBER() OVER (PARTITION BY escort_id ORDER BY FIELD(plan_code, "P101", "P102", "P103", "P104","P105","P106")) as rn
    //                 FROM subscriptions
    //                 WHERE end_date > NOW()
    //             ) t
    //             WHERE t.rn = 1
    //         ) as latest_subscription';
    //     }

    //     $result = $result->join(
    //         \DB::raw($rawSubQuary),
    //         'subscriptions.id',
    //         '=',
    //         'latest_subscription.max_id'
    //     )
    //         ->whereColumn('subscriptions.id', '=', 'latest_subscription.max_id');



    //     $result = $result->leftJoin('locations', 'profile.city_id', '=', 'locations.id')
    //         ->selectRaw('locations.id, COUNT(*) as subscription_count,locations.name as city_name,locations.type as location_type,locations.slug as slug');

    //     // $byPlanOrder = filter_var($request->query('byPlanOrder'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    //     // if ($byPlanOrder) {
    //     //     $rawSubQuary = '(SELECT escort_id, MAX(end_date) as latest_end_date, MAX(id)
    //     //     as max_id FROM subscriptions GROUP BY escort_id) as latest_subscription';
    //     // }else{
    //     //     $rawSubQuary = '(SELECT escort_id, MAX(end_date) as latest_end_date, MAX(id)
    //     //     as max_id FROM subscriptions GROUP BY escort_id, plan_code) as latest_subscription';
    //     // }




    //     // $result = $result->join(                                                                                                                                                   
    //     //     \DB::raw($rawSubQuary),
    //     //     'subscriptions.id',
    //     //     '=',                                                                                                                                                                                                                                                                                                                                                                                    
    //     //     'latest_subscription.max_id'
    //     // )
    //     //     ->whereColumn('subscriptions.id', '=', 'latest_subscription.max_id');

    //     $result = $result->groupBy('locations.id', 'locations.name', 'locations.slug', 'locations.type');
    //     return Resp::success(["list" => $result->get()]);
    // }

    public function topLocation(Request $request)
    {
        $primaryLocations = EscortSubscription::join('profile', 'subscriptions.escort_id', '=', 'profile.escort_id')
            ->where('subscriptions.end_date', '>', now())
            ->where('subscriptions.is_hidden', 0)
            ->whereHas('escort.profile', function($query) {
                $query->where('verified_status', 1);
            });

        $byPlanOrder = filter_var($request->query('byPlanOrder'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($byPlanOrder) {
            //     $rawSubQuary = '(SELECT escort_id, MAX(end_date) as latest_end_date, MAX(id)
            // as max_id FROM subscriptions GROUP BY escort_id) as latest_subscription';

            $rawSubQuary = '(SELECT escort_id, MAX(end_date) as latest_end_date, MAX(id)
        as max_id FROM subscriptions GROUP BY escort_id, plan_code) as latest_subscription';
        } else {
            $rawSubQuary = '(
            SELECT t.escort_id, t.latest_end_date, t.max_id
            FROM (
                SELECT escort_id, end_date as latest_end_date, id as max_id,
                       ROW_NUMBER() OVER (PARTITION BY escort_id ORDER BY FIELD(plan_code, "P101", "P102", "P103", "P104","P105","P106")) as rn
                FROM subscriptions
                WHERE end_date > NOW()
            ) t
            WHERE t.rn = 1
        ) as latest_subscription';
        }





        $primaryLocations = $primaryLocations->join(
            \DB::raw($rawSubQuary),
            'subscriptions.id',
            '=',
            'latest_subscription.max_id'
        )
            ->whereColumn('subscriptions.id', '=', 'latest_subscription.max_id')
            ->leftJoin('locations', 'profile.city_id', '=', 'locations.id')
            ->selectRaw('locations.id, COUNT(*) as subscription_count, locations.name as city_name, locations.type as location_type, locations.slug as slug')
            ->groupBy('locations.id', 'locations.name', 'locations.slug', 'locations.type');

        // $rawSubQuary2 = '(SELECT escort_id, MAX(end_date) as latest_end_date, MAX(id)
        // as max_id FROM subscriptions GROUP BY escort_id, plan_code) as latest_subscription';


        $rawSubQuary2 = '(SELECT escort_id, MAX(end_date) as latest_end_date, MAX(id)
        as max_id FROM subscriptions GROUP BY escort_id) as latest_subscription';

        // Second query for extra locations
        $extraLocations = EscortSubscription::join('profile', 'subscriptions.escort_id', '=', 'profile.escort_id')
            ->where('subscriptions.end_date', '>', now())
            ->where('subscriptions.is_hidden', 0)
            ->whereHas('escort.profile', function($query) {
                $query->where('verified_status', 1);
            })
            ->join(
                \DB::raw($rawSubQuary),
                'subscriptions.id',
                '=',
                'latest_subscription.max_id'
            )
            ->whereColumn('subscriptions.id', '=', 'latest_subscription.max_id')
            // ->crossJoin('locations')
            ->leftJoin('locations', 'locations.id', '=', 'locations.id') // Change from crossJoin to leftJoin
            ->whereRaw('JSON_CONTAINS(subscriptions.extra_location, CAST(locations.id AS CHAR))')
            // ->selectRaw('locations.id, COUNT(*) as subscription_count, locations.name as city_name, locations.type as location_type, locations.slug as slug')
            ->selectRaw('locations.id, COUNT(*) as subscription_count, locations.name as city_name, locations.type as location_type, locations.slug as slug')

            ->groupBy('locations.id', 'locations.name', 'locations.slug', 'locations.type');

        // Convert Eloquent builders to Query builders
        $primaryLocationsQuery = $primaryLocations->toBase();
        $extraLocationsQuery = $extraLocations->toBase();

        // Combine results using UNION ALL
        $result = $primaryLocationsQuery->union($extraLocationsQuery);

        // Sum up the counts for duplicate locations
        $finalResult = \DB::table(\DB::raw("({$result->toSql()}) as combined"))
            ->mergeBindings($result)
            ->selectRaw('id, SUM(subscription_count) as subscription_count, city_name, location_type, slug')
            ->groupBy('id', 'city_name', 'location_type', 'slug');

        return Resp::success(["list" => $finalResult->get()]);
    }




    public function getSubscriptions(Request $request)
    {
        try {
            $user = auth()->user();
            $locationType = "";
            $searchedlocationType="";
            $subscriptions = EscortSubscription::query();



            $subscriptions->leftJoin('plans', 'subscriptions.plan_code', '=', 'plans.code')
                ->select('subscriptions.*', 'plans.title as plan_title')
                ->where('subscriptions.end_date', '>', now())
                ->where('subscriptions.is_hidden', 0);

            if(!$request->query('ignore_verified')){
               
                $subscriptions->whereHas('escort.profile', function($query) {
                    $query->where('verified_status', 1);
                });
            }
            
                
            if ($request->query('slug')) {
                $slug = $request->query('slug');

                // $location = Location::where('slug', 'like', '%' . $slug . '%')->first();
                $location = Location::where('slug', $slug)->first();
                if (!$location) {
                    return Resp::error(["Slug location not found"]);
                }
                $type = $location->type;
                switch ($type) {
                    case 'city':
                        $locationType = 'city';
                        $request->merge(['city_id' => $location->id]);
                        break;
                    case 'county':
                        $locationType = 'county';
                        $request->merge(['county_id' => $location->id]);
                        break;
                    case 'region':
                        $locationType = 'region';
                        $request->merge(['region_id' => $location->id]);
                        break;
                }
            }

            if (!is_null($request->query('county_slug'))) {
                $countySlug = $request->query('county_slug');
                $subscriptions->whereHas('escort.profile', function ($query) use ($countySlug) {
                    $query->whereHas('county', function ($q) use ($countySlug) {
                        $q->where('slug', 'like', '%' . $countySlug . '%');
                    });
                });
            }

            if (!is_null($request->query('region_slug'))) {
                $regionSlug = $request->query('region_slug');
                $subscriptions->whereHas('escort.profile', function ($query) use ($regionSlug) {
                    $query->whereHas('region', function ($q) use ($regionSlug) {
                        $q->where('slug', 'like', '%' . $regionSlug . '%');
                    });
                });
            }

            if (!is_null($request->query('plan_code'))) {
                $subscriptions->where('plan_code', $request->query('plan_code'));
            }

            if ($request->query('plan_code') == 'P104') {
                $subscriptions->orderBy('sort_order'); // Sort by sort_order if plan_code is P104
            }

            // if (!is_null($request->query('escort_id'))) {
            //     $subscriptions->where('escort_id', $request->query('escort_id'));
            // }

            if (!is_null($request->query('ethnicity'))) {
                $subscriptions->whereHas('escort.profile', function ($query) use ($request) {
                    $query->where('ethnicity', $request->query('ethnicity'));
                });
            }

            if (!is_null($request->query('escort_id'))) {
                $subscriptions->whereHas('escort.profile', function ($query) use ($request) {
                    $query->where('escort_id', $request->query('escort_id'));
                });
            }

            if (!is_null($request->query('subscription_id'))) {
                $subscriptions->where('subscriptions.id', $request->query('subscription_id'));
            }


            if (!is_null($request->query('cock_size'))) {
                $subscriptions->whereHas('escort.profile', function ($query) use ($request) {
                    $query->where('cock_size', $request->query('cock_size'));
                });
            }

            if (!is_null($request->query('status'))) {
                if ($request->query('status') == 'active') {
                    $subscriptions->where('end_date', '>', now());
                } elseif ($request->query('status') == 'expired') {
                    $subscriptions->where('end_date', '<', now());
                }
            }

            if (!is_null($request->query('orientation'))) {
                $subscriptions->whereHas('escort.profile', function ($query) use ($request) {
                    $query->where('orientation', $request->query('orientation'));
                });
            }

            // if(!is_null($request->query('locationName'))){
            //     $locationORName = $request->query('locationName');
            //     // $searchedlocationType=null;

            //     // Check if the location name exists in Region, County, or City (in order of priority)
            //     $regionExists = Location::where('name', 'like',  $locationORName . '%')
            //         ->where('type','region')->exists();
            //     if ($regionExists) {
            //         $searchedlocationType = 'region';
            //     } else {
            //         $countyExists = Location::where('name', 'like', $locationORName . '%')
            //             ->where('type','county')->exists();
            //     if ($countyExists) {
            //         $searchedlocationType = 'county';
            //     } else {
            //         $cityExists = Location::where('name', 'like',  $locationORName . '%')
            //             ->where('type','city')->exists();
            //     if ($cityExists) {
            //         $searchedlocationType = 'city';
            //         }
            //         }
            //     }

            //     $subscriptions->whereHas('escort.profile', function ($query) use ($locationORName) {
            //         $query->whereHas('city', function ($q) use ($locationORName) {
            //             $q->where('name', 'like',  $locationORName . '%');
            //         })->orWhereHas('region', function ($q) use ($locationORName) {
            //             $q->where('name', 'like',  $locationORName . '%');
            //         })->orWhereHas('county', function ($q) use ($locationORName) {
            //             $q->where('name', 'like',  $locationORName . '%');
            //         });
            //         });

            // }

            // if(!is_null($request->query('profileName'))){
            //     $profileName = $request->query('profileName');

            //     $subscriptions->whereHas('escort.profile', function ($query) use ($profileName) {
            //         $query->where('name', 'like',  $profileName . '%');
            //     })->orWhereHas('escort', function ($query) use ($profileName) {
            //         $query->where('username', 'like', $profileName . '%');
            //     });

            // }

            if (!is_null($profileName = $request->query('profileName')) || !is_null($location = $request->query('locationName'))) {
                $location= $request->query('locationName');
                $profileName= $request->query('profileName');
                $subscriptions->where(function ($query) use ($profileName, $location,&$searchedlocationType) {
                    // If profileName is provided, search in profile name or escort username
                    if (!is_null($profileName)) {
                        $query->where(function ($q) use ($profileName) {
                            $q->whereHas('escort.profile', function ($q) use ($profileName) {
                                $q->where('name', 'like', $profileName . '%');
                            })->orWhereHas('escort', function ($q) use ($profileName) {
                                $q->where('username', 'like', $profileName . '%');
                            });
                        });
                    }
            
                    // If location is provided, search in city, region, or county

                    // $searchedlocationType=null;

                    // Check if the location name exists in Region, County, or City (in order of priority)
                    $regionExists = Location::where('name', 'like',  $location . '%')
                        ->where('type','region')->exists();
                    if ($regionExists) {
                        $searchedlocationType = 'region';
                    } else {
                        $countyExists = Location::where('name', 'like', $location . '%')
                            ->where('type','county')->exists();
                        if ($countyExists) {
                            $searchedlocationType = 'county';
                        } else {
                            $cityExists = Location::where('name', 'like',  $location . '%')
                                ->where('type','city')->exists();
                            if ($cityExists) {
                                $searchedlocationType = 'city';
                            }
                        }
                    }
                    if (!is_null($location)) {
                        $query->where(function ($q) use ($location) {
                            $q->whereHas('escort.profile.city', function ($q) use ($location) {
                                $q->where('name', 'like', $location . '%');
                            })->orWhereHas('escort.profile.region', function ($q) use ($location) {
                                $q->where('name', 'like', $location . '%');
                            })->orWhereHas('escort.profile.county', function ($q) use ($location) {
                                $q->where('name', 'like', $location . '%');
                            });
                        });
                    }
                });
            }

            if (!is_null($request->query('rate'))) {
                $subscriptions->whereHas('escort.profile.rates', function ($query) use ($request) {
                    // Check for the selected rate type (e.g., '15_min', '30_min', etc.)


                    // Check for a specific rate value
                    if ($request->query('specific_rate')) {
                        $query->where('' . $request->query('rate_type') . '', "<=", $request->query('specific_rate'));
                    }

                    // Check for Incall or Outcall options
                    if ($request->query('incall') || $request->query('outcall')) {
                        $query->where(function ($q) use ($request) {
                            if ($request->query('incall')) {
                                $q->orWhere('category', 'Incall');
                            }
                            if ($request->query('outcall')) {
                                $q->orWhere('category', 'Outcall');
                            }
                        });
                    }
                });
            }

            // if (!is_null($request->query('city_id'))) {


            //     $subscriptions->where(function ($query) use ($request) {
            //         $query->whereHas('escort.profile', function ($query) use ($request) {
            //             $query->where('city_id', $request->query('city_id'));
            //         })
            //             ->orWhere(function ($query) use ($request) {
            //                 // Check if the city_id exists in the extra_location JSON column
            //                 $query->whereJsonContains('extra_location', $request->query('city_id'));
            //             });
            //     });
            // }

            // if (!is_null($request->query('region_id'))) {

            //     $subscriptions->whereHas('escort.profile', function ($query) use ($request) {
            //         $query->where('region_id', $request->query('region_id'));
            //     })
            //         ->orWhere(function ($query) use ($request) {
            //             // Check if the city_id exists in the extra_location JSON column
            //             $query->whereJsonContains('extra_location', $request->query('region_id'));
            //         });
            // }

            // if (!is_null($request->query('county_id'))) {

            //     $subscriptions->whereHas('escort.profile', function ($query) use ($request) {
            //         $query->where('county_id', $request->query('county_id'));
            //     })
            //         ->orWhere(function ($query) use ($request) {
            //             // Check if the city_id exists in the extra_location JSON column
            //             $query->whereJsonContains('extra_location', $request->query('county_id'));
            //         });
            // }

            if (!is_null($request->query('city_id'))) {
                $subscriptions->where(function ($query) use ($request) {
                    $query->whereHas('escort.profile', function ($query) use ($request) {
                        $query->where('city_id', $request->query('city_id'));
                    })
                        // // Only apply extra_location filter if necessary (e.g., for records that don't use city_id)
                        ->orWhere(function ($query) use ($request) {
                            $query->whereRaw('JSON_CONTAINS(subscriptions.extra_location, CAST(? AS CHAR))', [$request->query('city_id')]);
                        });
                    //    ->orWhereRaw('JSON_CONTAINS(subscriptions.extra_location, CAST(? AS CHAR))', [$request->query('city_id')]);
                });
            }

            if (!is_null($request->query('region_id'))) {
                $subscriptions->where(function ($query) use ($request) {
                    $query->whereHas('escort.profile', function ($query) use ($request) {
                        $query->where('region_id', $request->query('region_id'));
                    })
                        ->orWhereHas('extraLocations', function ($query) use ($request) {
                            $query->where('region_id', $request->query('region_id'));
                        })
                        ->orWhereRaw('JSON_CONTAINS(subscriptions.extra_location, CAST(? AS CHAR))', [$request->query('region_id')]);
                });
            }

            if (!is_null($request->query('county_id'))) {

                $subscriptions->where(function ($query) use ($request) {
                    $query->whereHas('escort.profile', function ($query) use ($request) {
                        $query->where('county_id', $request->query('county_id'));
                    })
                        ->orWhereHas('extraLocations', function ($query) use ($request) {
                            $query->where('county_id', $request->query('county_id'));
                        })
                        ->orWhereRaw('JSON_CONTAINS(subscriptions.extra_location, CAST(? AS CHAR))', [$request->query('county_id')]);
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

            $byPlanOrder = filter_var($request->query('byPlanOrder'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $currentWeek = $request->query('current_week', false); // Check if "current_week" is passed


            if($currentWeek){
                $weekStart = now()->startOfWeek(); // Monday 
                $weekEnd = now()->endOfWeek(); // Sunday
                //die($weekStart->toDateTimeString() . ' - ' . $weekEnd->toDateTimeString());
                $subscriptions->where('subscriptions.plan_code', 'P101')
                ->where('subscriptions.start_date', '<=', $weekEnd) // Starts before the week ends
                ->where('subscriptions.end_date', '>=', $weekStart); // Ends after the week starts
        
            }

            //for edit modal on plans page
            $tsweek_modal=$request->query("tsweek_modal");
            if($tsweek_modal){
                $weekStart = now()->startOfWeek(); // Monday 
                $weekEnd = now()->endOfWeek(); // Sunday
                
                echo($weekStart->toDateTimeString() . ' - ' . $weekEnd->toDateTimeString());
                $subscriptions->where('subscriptions.plan_code', 'P101')
                              ->where(function($query) use ($weekStart, $weekEnd) {
                               $query->where(function($q) use ($weekStart, $weekEnd) {
                                  $q->where('subscriptions.start_date', '<=', $weekEnd)
                                     ->where('subscriptions.end_date', '>=', $weekStart);
                                   })
                                ->orWhere(function($q) use ($weekStart) {
                                   $q->where('subscriptions.start_date', '>=', $weekStart);
                                  
                                  //  ->limit(1);     
                                  });
                 })
                 ->orderBy('subscriptions.start_date', 'asc');
                 //->limit(1);
            }

            
            if ($byPlanOrder) {

                // $rawSubQuary = '(SELECT escort_id, MAX(end_date) as latest_end_date, MAX(id)
                // as max_id FROM subscriptions GROUP BY escort_id) as latest_subscription';
                // $rawSubQuary = '(SELECT escort_id, MAX(end_date) as latest_end_date, MAX(id)
                // as max_id FROM subscriptions GROUP BY escort_id, plan_code) as latest_subscription';
                $rawSubQuary = '(SELECT escort_id, MAX(end_date) as latest_end_date, MAX(id)
                    as max_id FROM subscriptions GROUP BY escort_id, plan_code) as latest_subscription';
                
            } else {
                // $rawSubQuary = '(SELECT escort_id, MAX(end_date) as latest_end_date, MAX(id)
                // as max_id FROM subscriptions GROUP BY escort_id, plan_code) as latest_subscription';


                $rawSubQuary = '(
                    SELECT t.escort_id, t.latest_end_date, t.max_id
                    FROM (
                        SELECT escort_id, end_date as latest_end_date, id as max_id,
                               ROW_NUMBER() OVER (PARTITION BY escort_id ORDER BY FIELD(plan_code, "P101", "P102", "P103", "P104","P105","P106")) as rn
                        FROM subscriptions
                        WHERE end_date > NOW()
                    ) t
                    WHERE t.rn = 1
                ) as latest_subscription';
            }

            if(!$currentWeek && $tsweek_modal!=true){
                
                $subscriptions->join(
                    \DB::raw($rawSubQuary),
                    'subscriptions.id',
                    '=',
                    'latest_subscription.max_id'

                )->whereColumn('subscriptions.id', '=', 'latest_subscription.max_id');
                }

            $perPage = $request->query('per_page', 18);
            $page = $request->query('page', 1);
            $offset = ($page - 1) * $perPage;


            $totalCount = $subscriptions->count();

            $result = $subscriptions->with([
                'escort',
                'escort.profile.county',
                'escort.profile.city',
                'escort.profile.region',
                'escort.profile.reviews',
                'escort.profile.media',
                'escort.profile.rates',
                'orders',
                'media'
            ])

                ->orderByRaw('CASE WHEN created_mode IS NOT NULL THEN 0 ELSE 1 END, created_at DESC')
                ->orderBy("plan_code", "asc")
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



            return Resp::success(["list" => $result,'searched_location_type' => $searchedlocationType, 'location_type' => $locationType, 'pagination' => ['total_results' => $totalCount, 'total_pages' => ceil($totalCount / $perPage), 'page_number' => $page, 'page_size' => $perPage]]);

            // Retrieve subscriptions with related escort and profile
        } catch (\Exception $e) {
            return Resp::error(['message' => 'Something went wrong' . $e->getMessage()]);
        }
    }




    //function for getting adverts list on dashboard
    public function getAdvertLists(Request $request)
    {
        try {
            $user = auth()->user();
            $locationType = "";
            $subscriptions = EscortSubscription::query();
            $s = $request->query('s');



            $subscriptions->leftJoin('plans', 'subscriptions.plan_code', '=', 'plans.code')
                ->select('subscriptions.*', 'plans.title as plan_title')
                ->where('subscriptions.end_date', '>', now());
            if ($request->query('slug')) {
                $slug = $request->query('slug');

                $location = Location::where('slug', 'like', '%' . $slug . '%')->first();
                $type = $location->type;
                switch ($type) {
                    case 'city':
                        $locationType = 'city';
                        $request->merge(['city_id' => $location->id]);
                        break;
                    case 'county':
                        $locationType = 'county';
                        $request->merge(['county_id' => $location->id]);
                        break;
                    case 'region':
                        $locationType = 'region';
                        $request->merge(['region_id' => $location->id]);
                        break;
                }
            }

            if (!is_null($request->query('s'))) {
                $subscriptions->whereHas('escort.profile', function ($query) use ($request) {
                    $query->where('username', 'like', '%' . $request->query('s') . '%');
                });
            }

            if (!is_null($request->query('county_slug'))) {
                $countySlug = $request->query('county_slug');
                $subscriptions->whereHas('escort.profile', function ($query) use ($countySlug) {
                    $query->whereHas('county', function ($q) use ($countySlug) {
                        $q->where('slug', 'like', '%' . $countySlug . '%');
                    });
                });
            }

            if (!is_null($request->query('region_slug'))) {
                $regionSlug = $request->query('region_slug');
                $subscriptions->whereHas('escort.profile', function ($query) use ($regionSlug) {
                    $query->whereHas('region', function ($q) use ($regionSlug) {
                        $q->where('slug', 'like', '%' . $regionSlug . '%');
                    });
                });
            }

            if (!is_null($request->query('plan_code'))) {
                $subscriptions->where('plan_code', $request->query('plan_code'));
            }

            if ($request->query('plan_code') == 'P104') {
                $subscriptions->orderBy('sort_order'); // Sort by sort_order if plan_code is P104
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

            if (!is_null($request->query('status'))) {
                if ($request->query('status') == 'active') {
                    $subscriptions->where('end_date', '>', now());
                } elseif ($request->query('status') == 'expired') {
                    $subscriptions->where('end_date', '<', now());
                }
            }

            if (!is_null($request->query('orientation'))) {
                $subscriptions->whereHas('escort.profile', function ($query) use ($request) {
                    $query->where('orientation', $request->query('orientation'));
                });
            }

            if (!is_null($request->query('end_date'))) {
                $subscriptions->where('end_date',$request->query('end_date'));
            }




            if (!is_null($request->query('city_id'))) {


                $subscriptions->where(function ($query) use ($request) {
                    $query->whereHas('escort.profile', function ($query) use ($request) {
                        $query->where('city_id', $request->query('city_id'));
                    })
                        // ->orWhere(function ($query) use ($request) {
                        //      $query->whereRaw('JSON_CONTAINS(subscriptions.extra_location, CAST(? AS CHAR))', [$request->query('city_id')]);
                        //  });
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

            $perPage = $request->query('per_page', 15);
            $page = $request->query('page', 1);
            $offset = ($page - 1) * $perPage;

            $totalCount = $subscriptions->count();

            $result = $subscriptions->with([
                'escort',
                'escort.profile.county',
                'escort.profile.city',
                'escort.profile.region',
                'escort.profile.reviews',
                'escort.profile.media',
                'escort.profile.rates',
                'orders',
                'media'
            ])

                ->orderByRaw('CASE WHEN created_mode IS NOT NULL THEN 0 ELSE 1 END, created_at DESC')
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



            return Resp::success(["list" => $result, 'location_type' => $locationType, 'pagination' => ['total_results' => $totalCount, 'total_pages' => ceil($totalCount / $perPage), 'page_number' => $page, 'page_size' => $perPage]]);

            // Retrieve subscriptions with related escort and profile
        } catch (\Exception $e) {
            return Resp::error(['message' => 'Something went wrong' . $e->getMessage()]);
        }
    }


    // public function slugToLocation(Request $request)
    // {
    //     $slug = $request->input('slug');
    //     $location = Location::where('slug', $slug)->first();

    //     $byPlanOrder = filter_var($request->query('byPlanOrder'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    //     if ($byPlanOrder) {
    //         $rawSubQuary = '(SELECT escort_id, MAX(end_date) as latest_end_date, MAX(id)
    //                     as max_id FROM subscriptions GROUP BY escort_id) as latest_subscription';
    //     } else {
    //         // $rawSubQuary = '(SELECT escort_id, MAX(end_date) as latest_end_date, MAX(id)
    //         //             as max_id FROM subscriptions GROUP BY escort_id, plan_code) as latest_subscription';


    //         $rawSubQuary = '(
    //             SELECT t.escort_id, t.latest_end_date, t.max_id
    //             FROM (
    //                 SELECT escort_id, end_date as latest_end_date, id as max_id,
    //                        ROW_NUMBER() OVER (PARTITION BY escort_id ORDER BY FIELD(plan_code, "P101", "P102", "P103", "P104","P105","P106")) as rn
    //                 FROM subscriptions
    //                 WHERE end_date > NOW()
    //             ) t
    //             WHERE t.rn = 1
    //         ) as latest_subscription';
    //     }

    //     if ($location) {
    //         if ($location->type == 'city') {
    //             $county = Location::where('id', $location->parent_id)->first();
    //             $region = Location::where('id', $county->parent_id)->first();
    //             $city_data = EscortSubscription::join('profile', 'subscriptions.escort_id', '=', 'profile.escort_id')
    //                 // ->where('subscriptions.end_date', '>', now())
    //                 // ->where('subscriptions.is_hidden', 0)
    //                 ->leftJoin('locations', 'profile.city_id', '=', 'locations.id')
    //                 ->where('profile.city_id', $location->id)
    //                 ->selectRaw('COUNT(*) as subscription_count,locations.name as location_name')
    //                 ->groupBy('profile.city_id', 'locations.name')
    //                 ->first();

    //             $city_data = EscortSubscription::join('profile', 'subscriptions.escort_id', '=', 'profile.escort_id')
    //                 ->where('profile.city_id', $location->id)
    //                 ->where('subscriptions.end_date', '>', now())
    //                 ->where('subscriptions.is_hidden', 0)
    //                 ->where(function($query) use ($location) {
    //                     $query->where('profile.city_id', $location->id)
    //                           ->orWhereRaw('JSON_CONTAINS(subscriptions.extra_location, CAST(? AS CHAR))', [$location->id]);
    //                 });

    //             $city_data = $city_data->join(
    //                 \DB::raw($rawSubQuary),
    //                 'subscriptions.id',
    //                 '=',
    //                 'latest_subscription.max_id'
    //             )
    //                 ->whereColumn('subscriptions.id', '=', 'latest_subscription.max_id');

    //             $city_data = $city_data->selectRaw('COUNT(*) as subscription_count')
    //                 ->first();


    //             $county_data = EscortSubscription::join('profile', 'subscriptions.escort_id', '=', 'profile.escort_id')
    //                 ->where('profile.county_id', $county->id)
    //                 ->where('subscriptions.end_date', '>', now())
    //                 ->where('subscriptions.is_hidden', 0)
    //                 ->where(function($query) use ($county) {
    //                     $query->where('profile.county_id', $county->id)
    //                           ->orWhereRaw('JSON_CONTAINS(subscriptions.extra_location, CAST(? AS CHAR))', [$county->id]);
    //                 });


    //             $county_data = $county_data->join(                                                                                                                       
    //                 \DB::raw($rawSubQuary),
    //                 'subscriptions.id',
    //                 '=',
    //                 'latest_subscription.max_id'
    //             )
    //                 ->whereColumn('subscriptions.id', '=', 'latest_subscription.max_id');


    //             $county_data = $county_data->selectRaw('COUNT(*) as subscription_count')
    //                 ->first();


    //             $region_data = EscortSubscription::join('profile', 'subscriptions.escort_id', '=', 'profile.escort_id')
    //                 ->where('profile.region_id', $region->id)
    //                 ->where('subscriptions.end_date', '>', now())
    //                 ->where('subscriptions.is_hidden', 0)
    //                 ->where(function($query) use ($region) {
    //                     $query->where('profile.region_id', $region->id)
    //                           ->orWhereRaw('JSON_CONTAINS(subscriptions.extra_location, CAST(? AS CHAR))', [$region->id]);
    //                 });

    //             $region_data = $region_data->join(
    //                 \DB::raw($rawSubQuary),
    //                 'subscriptions.id',
    //                 '=',
    //                 'latest_subscription.max_id'
    //             )
    //                 ->whereColumn('subscriptions.id', '=', 'latest_subscription.max_id');

    //             $region_data = $region_data->selectRaw('COUNT(*) as subscription_count')
    //                 ->first();

    //             $location['subscription_count'] = $city_data->subscription_count;
    //             $county['subscription_count'] = $county_data->subscription_count;
    //             $region['subscription_count'] = $region_data->subscription_count;
    //             return Resp::success(['city' => $city_data, 'county' => $county_data, 'location_type' => $location->type, 'data' => ['county' => $county, 'region' => $region, 'city' => $location]]);
    //         } elseif ($location->type == 'county') {
    //             $region = Location::where('id', $location->parent_id)->first();

    //             $county_data = EscortSubscription::join('profile', 'subscriptions.escort_id', '=', 'profile.escort_id')
    //                 ->where('profile.county_id', $location->id)
    //                 ->where('subscriptions.end_date', '>', now())
    //                 ->where('subscriptions.is_hidden', 0)
    //                 ->where(function($query) use ($location) {
    //                     $query->where('profile.county_id', $location->id)
    //                           ->orWhereRaw('JSON_CONTAINS(subscriptions.extra_location, CAST(? AS CHAR))', [$location->id]);
    //                 });

    //             $county_data = $county_data->join(
    //                 \DB::raw($rawSubQuary),
    //                 'subscriptions.id',
    //                 '=',
    //                 'latest_subscription.max_id'
    //             )
    //                 ->whereColumn('subscriptions.id', '=', 'latest_subscription.max_id');
    //             $county_data = $county_data->selectRaw('COUNT(*) as subscription_count')
    //                 ->first();


    //             $region_data = EscortSubscription::join('profile', 'subscriptions.escort_id', '=', 'profile.escort_id')
    //                 ->where('profile.region_id', $region->id)
    //                 ->where('subscriptions.end_date', '>', now())
    //                 ->where('subscriptions.is_hidden', 0)
    //                 ->where(function($query) use ($region) {
    //                     $query->where('profile.region_id', $region->id)
    //                           ->orWhereRaw('JSON_CONTAINS(subscriptions.extra_location, CAST(? AS CHAR))', [$region->id]);
    //                 });

    //             $region_data = $region_data->join(
    //                 \DB::raw($rawSubQuary),
    //                 'subscriptions.id',
    //                 '=',
    //                 'latest_subscription.max_id'
    //             )
    //                 ->whereColumn('subscriptions.id', '=', 'latest_subscription.max_id');

    //             $region_data = $region_data->selectRaw('COUNT(*) as subscription_count')
    //                 ->first();

    //             $location['subscription_count'] = $county_data->subscription_count;
    //             $region['subscription_count'] = $region_data->subscription_count;

    //             return Resp::success(['location_type' => $location->type, 'data' => ['county' => $location, 'region' => $region]]);
    //         } else {
    //             $region = $location;
    //         }
    //     }


    //     $region_data = EscortSubscription::join('profile', 'subscriptions.escort_id', '=', 'profile.escort_id')
    //         ->where('profile.region_id', $location->id)
    //         ->where('subscriptions.end_date', '>', now())
    //         ->where('subscriptions.is_hidden', 0)
    //         ->where(function($query) use ($location) {
    //             $query->where('profile.region_id', $location->id)
    //                   ->orWhereRaw('JSON_CONTAINS(subscriptions.extra_location, CAST(? AS CHAR))', [$location->id]);
    //         });

    //     $region_data = $region_data->join(
    //         \DB::raw($rawSubQuary),
    //         'subscriptions.id',
    //         '=',
    //         'latest_subscription.max_id'
    //     )
    //         ->whereColumn('subscriptions.id', '=', 'latest_subscription.max_id');

    //     $region_data = $region_data->selectRaw('COUNT(*) as subscription_count')
    //         ->first();
    //     $region['subscription_count'] = $region_data->subscription_count;

    //     return Resp::success(['location_type' => $location->type, 'data' => ['region' => $region]]);
    // }

    public function slugToLocation(Request $request)
    {
        $slug = $request->input('slug');
        $location = Location::where('slug', $slug)->first();

        $byPlanOrder = filter_var($request->query('byPlanOrder'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($byPlanOrder) {
            // $rawSubQuary = '(SELECT escort_id, MAX(end_date) as latest_end_date, MAX(id)
            // as max_id FROM subscriptions GROUP BY escort_id) as latest_subscription';
            $rawSubQuary = '(SELECT escort_id, MAX(end_date) as latest_end_date, MAX(id)
                as max_id FROM subscriptions GROUP BY escort_id, plan_code) as latest_subscription';
        } else {
            $rawSubQuary = '(
            SELECT t.escort_id, t.latest_end_date, t.max_id
            FROM (
                SELECT escort_id, end_date as latest_end_date, id as max_id,
                       ROW_NUMBER() OVER (PARTITION BY escort_id ORDER BY FIELD(plan_code, "P101", "P102", "P103", "P104","P105","P106")) as rn
                FROM subscriptions
                WHERE end_date > NOW()
            ) t
            WHERE t.rn = 1
        ) as latest_subscription';
        }

        if ($location) {
            if ($location->type == 'city') {
                $county = Location::where('id', $location->parent_id)->first();
                $region = Location::where('id', $county->parent_id)->first();

                $city_data = EscortSubscription::join('profile', 'subscriptions.escort_id', '=', 'profile.escort_id')
                    ->where('subscriptions.end_date', '>', now())
                    ->where('subscriptions.is_hidden', 0)
                    ->where(function ($query) use ($location) {
                        $query->where('profile.city_id', $location->id)
                            ->orWhereRaw('JSON_CONTAINS(subscriptions.extra_location, CAST(? AS CHAR))', [$location->id]);

                    })->whereHas('escort.profile', function($query) {
                        $query->where('verified_status', 1);
                    });


                        



                    // Function to apply common filters
        $applyFilters = function ($query) use ($request) {
            if (!is_null($request->query('rate'))) {
                $query->whereHas('escort.profile.rates', function ($q) use ($request) {
                    if ($request->query('specific_rate')) {
                        $q->where($request->query('rate_type'), "<=", $request->query('specific_rate'));
                    }
                    if ($request->query('incall') || $request->query('outcall')) {
                        $q->where(function ($q) use ($request) {
                            if ($request->query('incall')) {
                                $q->orWhere('category', 'Incall');
                            }
                            if ($request->query('outcall')) {
                                $q->orWhere('category', 'Outcall');
                            }
                        });
                    }
                });
            }};




                // if (!is_null($request->query('rate'))) {
                //     $city_data->whereHas('escort.profile.rates', function ($query) use ($request) {
                //         // Check for the selected rate type (e.g., '15_min', '30_min', etc.)


                //         // Check for a specific rate value
                //         if ($request->query('specific_rate')) {
                //             $query->where('' . $request->query('rate_type') . '', "<=", $request->query('specific_rate'));
                //         }

                //         // Check for Incall or Outcall options
                //         if ($request->query('incall') || $request->query('outcall')) {
                //             $query->where(function ($q) use ($request) {
                //                 if ($request->query('incall')) {
                //                     $q->orWhere('category', 'Incall');
                //                 }
                //                 if ($request->query('outcall')) {
                //                     $q->orWhere('category', 'Outcall');
                //                 }
                //             });
                //         }
                //     });
                // }

                $applyFilters($city_data);

                if(!is_null($request->query('locationName'))){
                $locationName = $request->query('locationName');

                $city_data->whereHas('escort.profile', function ($query) use ($locationName) {
                    $query->whereHas('city', function ($q) use ($locationName) {
                        $q->where('name', 'like',  $locationName . '%');
                    })->orWhereHas('region', function ($q) use ($locationName) {
                        $q->where('name', 'like',  $locationName . '%');
                    })->orWhereHas('county', function ($q) use ($locationName) {
                        $q->where('name', 'like',  $locationName . '%');
                    });
                    });
                }

                if (!is_null($request->query('profileName'))) {
                    $city_data->whereHas('escort.profile', function ($query) use ($request) {
                        $query->where('name','like', $request->query('profileName').'%');
                    })->orWhereHas('escort', function ($query) use ($request) {
                        $query->where('username', 'like',$request->query('profileName').'%');
                    });
                }

                if (!is_null($request->query('plan_code'))) {
                    $city_data->where('plan_code', $request->query('plan_code'));
                }
                

                if (!is_null($request->query('ethnicity'))) {
                    $city_data->whereHas('escort.profile', function ($query) use ($request) {
                        $query->where('ethnicity', $request->query('ethnicity'));
                    });
                }
    
                if (!is_null($request->query('cock_size'))) {
                    $city_data->whereHas('escort.profile', function ($query) use ($request) {
                        $query->where('cock_size', $request->query('cock_size'));
                    });
                }
                if (!is_null($request->query('orientation'))) {
                    $city_data->whereHas('escort.profile', function ($query) use ($request) {
                        $query->where('orientation', $request->query('orientation'));
                    });
                }

                $city_data = $city_data->join(
                    \DB::raw($rawSubQuary),
                    'subscriptions.id',
                    '=',
                    'latest_subscription.max_id'
                )
                    ->whereColumn('subscriptions.id', '=', 'latest_subscription.max_id')
                    ->selectRaw('COUNT(DISTINCT subscriptions.escort_id) as subscription_count')
                    ->first();

                $county_data = EscortSubscription::join('profile', 'subscriptions.escort_id', '=', 'profile.escort_id')
                    ->where('subscriptions.end_date', '>', now())
                    ->where('subscriptions.is_hidden', 0)
                    ->where(function ($query) use ($county) {
                        $query->where('profile.county_id', $county->id)
                            ->orWhereRaw('JSON_CONTAINS(subscriptions.extra_location, CAST(? AS CHAR))', [$county->id])
                            ->orWhereHas('extraLocations', function ($q) use ($county) {
                                $q->where('county_id', $county->id);
                            });
                    })->whereHas('escort.profile', function($query) {
                        $query->where('verified_status', 1);
                    })
                    // ->orWhereHas('extraLocations', function ($query) use ($county) {
                    //     $query->where('county_id', $county->id);
                    // })
                    ;


                    // if (!is_null($request->query('rate'))) {
                    //     $county_data->whereHas('escort.profile.rates', function ($query) use ($request) {
                    //         // Check for the selected rate type (e.g., '15_min', '30_min', etc.)
    
    
                    //         // Check for a specific rate value
                    //         if ($request->query('specific_rate')) {
                    //             $query->where('' . $request->query('rate_type') . '', "<=", $request->query('specific_rate'));
                    //         }
    
                    //         // Check for Incall or Outcall options
                    //         if ($request->query('incall') || $request->query('outcall')) {
                    //             $query->where(function ($q) use ($request) {
                    //                 if ($request->query('incall')) {
                    //                     $q->orWhere('category', 'Incall');
                    //                 }
                    //                 if ($request->query('outcall')) {
                    //                     $q->orWhere('category', 'Outcall');
                    //                 }
                    //             });
                    //         }
                    //     });
                    // }
                    $applyFilters($county_data);

                    if(!is_null($request->query('locationName'))){
                    $locationName = $request->query('locationName');

                    $county_data->whereHas('escort.profile', function ($query) use ($locationName) {
                    $query->whereHas('city', function ($q) use ($locationName) {
                        $q->where('name', 'like',  $locationName . '%');
                    })->orWhereHas('region', function ($q) use ($locationName) {
                        $q->where('name', 'like',  $locationName . '%');
                    })->orWhereHas('county', function ($q) use ($locationName) {
                        $q->where('name', 'like',  $locationName . '%');
                    });
                    });
                }

                    if (!is_null($request->query('plan_code'))) {
                        $county_data->where('plan_code', $request->query('plan_code'));
                    }

                    if (!is_null($request->query('profileName'))) {
                        $county_data->whereHas('escort.profile', function ($query) use ($request) {
                            $query->where('name', 'like', $request->query('profileName') . '%');
                        })->orWhereHas('escort', function ($query) use ($request) {
                            $query->where('username', 'like', $request->query('profileName') . '%');
                        });
                    }
                    

                    if (!is_null($request->query('ethnicity'))) {
                        $county_data->whereHas('escort.profile', function ($query) use ($request) {
                            $query->where('ethnicity', $request->query('ethnicity'));
                        });
                    }
        
                    if (!is_null($request->query('cock_size'))) {
                        $county_data->whereHas('escort.profile', function ($query) use ($request) {
                            $query->where('cock_size', $request->query('cock_size'));
                        });
                    }
                    if (!is_null($request->query('orientation'))) {
                        $county_data->whereHas('escort.profile', function ($query) use ($request) {
                            $query->where('orientation', $request->query('orientation'));
                        });
                    }

                $county_data = $county_data->join(
                    \DB::raw($rawSubQuary),
                    'subscriptions.id',
                    '=',
                    'latest_subscription.max_id'
                )
                    ->whereColumn('subscriptions.id', '=', 'latest_subscription.max_id')
                    ->selectRaw('COUNT(DISTINCT subscriptions.escort_id) as subscription_count')
                    ->first();

                $region_data = EscortSubscription::join('profile', 'subscriptions.escort_id', '=', 'profile.escort_id')
                    ->where('subscriptions.end_date', '>', now())
                    ->where('subscriptions.is_hidden', 0)
                    ->where(function ($query) use ($region) {
                        $query->where('profile.region_id', $region->id)
                            ->orWhereRaw('JSON_CONTAINS(subscriptions.extra_location, CAST(? AS CHAR))', [$region->id])
                            ->orWhereHas('extraLocations', function ($query) use ($region) {
                                $query->where('region_id', $region->id);
                            });
                        })->whereHas('escort.profile', function($query) {
                            $query->where('verified_status', 1);
                        });
                    


                    // if (!is_null($request->query('rate'))) {
                    //     $region_data->whereHas('escort.profile.rates', function ($query) use ($request) {
                    //         // Check for the selected rate type (e.g., '15_min', '30_min', etc.)
    
    
                    //         // Check for a specific rate value
                    //         if ($request->query('specific_rate')) {
                    //             $query->where('' . $request->query('rate_type') . '', "<=", $request->query('specific_rate'));
                    //         }
    
                    //         // Check for Incall or Outcall options
                    //         if ($request->query('incall') || $request->query('outcall')) {
                    //             $query->where(function ($q) use ($request) {
                    //                 if ($request->query('incall')) {
                    //                     $q->orWhere('category', 'Incall');
                    //                 }
                    //                 if ($request->query('outcall')) {
                    //                     $q->orWhere('category', 'Outcall');
                    //                 }
                    //             });
                    //         }
                    //     });
                    // }
                    $applyFilters($region_data);

                    if(!is_null($request->query('locationName'))){
                    $locationName = $request->query('locationName');

                    $region_data->whereHas('escort.profile', function ($query) use ($locationName) {
                        $query->whereHas('city', function ($q) use ($locationName) {
                            $q->where('name', 'like',  $locationName . '%');
                        })->orWhereHas('region', function ($q) use ($locationName) {
                            $q->where('name', 'like',  $locationName . '%');
                        })->orWhereHas('county', function ($q) use ($locationName) {
                            $q->where('name', 'like',  $locationName . '%');
                        });
                        });
                    }   

                    if (!is_null($request->query('profileName'))) {
                        $region_data->whereHas('escort.profile', function ($query) use ($request) {
                            $query->where('name', 'like', $request->query('profileName') . '%');
                        })->orWhereHas('escort', function ($query) use ($request) {
                            $query->where('username', 'like', $request->query('profileName') . '%');
                        });
                    }

                    if (!is_null($request->query('plan_code'))) {
                        $region_data->where('plan_code', $request->query('plan_code'));
                    }


                    if (!is_null($request->query('ethnicity'))) {
                        $region_data->whereHas('escort.profile', function ($query) use ($request) {
                            $query->where('ethnicity', $request->query('ethnicity'));
                        });
                    }
        
                    if (!is_null($request->query('cock_size'))) {
                        $region_data->whereHas('escort.profile', function ($query) use ($request) {
                            $query->where('cock_size', $request->query('cock_size'));
                        });
                    }
                    if (!is_null($request->query('orientation'))) {
                        $region_data->whereHas('escort.profile', function ($query) use ($request) {
                            $query->where('orientation', $request->query('orientation'));
                        });
                    }
                    

                $region_data = $region_data->join(
                    \DB::raw($rawSubQuary),
                    'subscriptions.id',
                    '=',
                    'latest_subscription.max_id'
                )
                    ->whereColumn('subscriptions.id', '=', 'latest_subscription.max_id')
                    ->selectRaw('COUNT(DISTINCT subscriptions.escort_id) as subscription_count')
                    ->first();




                // Check if `extra_location` refers to the city and adjust counts for county and region
                $city_in_extra_location = EscortSubscription::whereRaw('
                JSON_CONTAINS(extra_location, CAST(? AS CHAR))', [$location->id])
                    ->where('subscriptions.end_date', '>', now())
                    ->where('subscriptions.is_hidden', 0)
                    ->exists();

                if ($city_in_extra_location) {
                    // If city is in extra_location, increase the counts for county and region as well
                    // $county_data->subscription_count += $city_data->subscription_count;
                    // $region_data->subscription_count += $city_data->subscription_count;
                }


                $location['subscription_count'] = $city_data->subscription_count;
                $county['subscription_count'] = $county_data->subscription_count;
                $region['subscription_count'] = $region_data->subscription_count;

                return Resp::success([
                    'city' => $city_data,
                    'county' => $county_data,
                    'location_type' => $location->type,
                    'data' => [
                        'county' => $county,
                        'region' => $region,
                        'city' => $location
                    ]
                ]);

            } elseif ($location->type == 'county') {
                $region = Location::where('id', $location->parent_id)->first();

                $county_data = EscortSubscription::join('profile', 'subscriptions.escort_id', '=', 'profile.escort_id')
                    ->where('subscriptions.end_date', '>', now())
                    ->where('subscriptions.is_hidden', 0)
                    ->where(function ($query) use ($location) {
                        $query->where('profile.county_id', $location->id)
                            ->orWhereRaw('JSON_CONTAINS(subscriptions.extra_location, CAST(? AS CHAR))', [$location->id])
                            ->orWhereHas('extraLocations', function ($query) use ($location) {
                                $query->where('county_id', $location->id);
                            });
                    })->whereHas('escort.profile', function($query) {
                        $query->where('verified_status', 1);
                    });
                


                if (!is_null($request->query('rate'))) {
                    $county_data->whereHas('escort.profile.rates', function ($query) use ($request) {
                        // Check for the selected rate type (e.g., '15_min', '30_min', etc.)


                        // Check for a specific rate value
                        if ($request->query('specific_rate')) {
                            $query->where('' . $request->query('rate_type') . '', "<=", $request->query('specific_rate'));
                        }

                        // Check for Incall or Outcall options
                        if ($request->query('incall') || $request->query('outcall')) {
                            $query->where(function ($q) use ($request) {
                                if ($request->query('incall')) {
                                    $q->orWhere('category', 'Incall');
                                }
                                if ($request->query('outcall')) {
                                    $q->orWhere('category', 'Outcall');
                                }
                            });
                        }
                    });
                }


                if (!is_null($request->query('profileName'))) {
                    $county_data->whereHas('escort.profile', function ($query) use ($request) {
                        $query->where('name', 'like', $request->query('profileName') . '%');
                    })->orWhereHas('escort', function ($query) use ($request) {
                        $query->where('username', 'like', $request->query('profileName') . '%');
                    });
                }

                if(!is_null($request->query('locationName'))){
                    $locationName = $request->query('locationName');

                    $county_data->whereHas('escort.profile', function ($query) use ($locationName) {
                        $query->whereHas('city', function ($q) use ($locationName) {
                            $q->where('name', 'like',  $locationName . '%');
                        })->orWhereHas('region', function ($q) use ($locationName) {
                            $q->where('name', 'like',  $locationName . '%');
                        })->orWhereHas('county', function ($q) use ($locationName) {
                            $q->where('name', 'like',  $locationName . '%');
                        });
                        });
                    }

                if (!is_null($request->query('plan_code'))) {
                    $county_data->where('plan_code', $request->query('plan_code'));
                }

                if (!is_null($request->query('ethnicity'))) {
                    $county_data->whereHas('escort.profile', function ($query) use ($request) {
                        $query->where('ethnicity', $request->query('ethnicity'));
                    });
                }
    
                if (!is_null($request->query('cock_size'))) {
                    $county_data->whereHas('escort.profile', function ($query) use ($request) {
                        $query->where('cock_size', $request->query('cock_size'));
                    });
                }
                if (!is_null($request->query('orientation'))) {
                    $county_data->whereHas('escort.profile', function ($query) use ($request) {
                        $query->where('orientation', $request->query('orientation'));
                    });
                }

                $county_data = $county_data->join(
                    \DB::raw($rawSubQuary),
                    'subscriptions.id',
                    '=',
                    'latest_subscription.max_id'
                )
                    ->whereColumn('subscriptions.id', '=', 'latest_subscription.max_id')
                    ->selectRaw('COUNT(DISTINCT subscriptions.escort_id) as subscription_count')
                    ->first();

                $region_data = EscortSubscription::join('profile', 'subscriptions.escort_id', '=', 'profile.escort_id')
                    ->where('subscriptions.end_date', '>', now())
                    ->where('subscriptions.is_hidden', 0)
                    ->where(function ($query) use ($region) {
                        $query->where('profile.region_id', $region->id)
                            ->orWhereRaw('JSON_CONTAINS(subscriptions.extra_location, CAST(? AS CHAR))', [$region->id])
                            ->orWhereHas('extraLocations', function ($query) use ($region) {
                                $query->where('region_id', $region->id);
                            });
                    })->whereHas('escort.profile', function($query) {
                        $query->where('verified_status', 1);
                    });


                    if (!is_null($request->query('rate'))) {
                        $region_data->whereHas('escort.profile.rates', function ($query) use ($request) {
                            // Check for the selected rate type (e.g., '15_min', '30_min', etc.)
    
    
                            // Check for a specific rate value
                            if ($request->query('specific_rate')) {
                                $query->where('' . $request->query('rate_type') . '', "<=", $request->query('specific_rate'));
                            }
    
                            // Check for Incall or Outcall options
                            if ($request->query('incall') || $request->query('outcall')) {
                                $query->where(function ($q) use ($request) {
                                    if ($request->query('incall')) {
                                        $q->orWhere('category', 'Incall');
                                    }
                                    if ($request->query('outcall')) {
                                        $q->orWhere('category', 'Outcall');
                                    }
                                });
                            }
                        });
                    }


                    if (!is_null($request->query('plan_code'))) {
                        $region_data->where('plan_code', $request->query('plan_code'));
                    }




                    if (!is_null($request->query('profileName'))) {
                        $region_data->whereHas('escort.profile', function ($query) use ($request) {
                        $query->where('name', 'like', $request->query('profileName') . '%');
                    })->orWhereHas('escort', function ($query) use ($request) {
                        $query->where('username', 'like', $request->query('profileName') . '%');
                    });
                    }

                    if(!is_null($request->query('locationName'))){
                    $locationName = $request->query('locationName');

                    $region_data->whereHas('escort.profile', function ($query) use ($locationName) {
                        $query->whereHas('city', function ($q) use ($locationName) {
                            $q->where('name', 'like',  $locationName . '%');
                        })->orWhereHas('region', function ($q) use ($locationName) {
                            $q->where('name', 'like',  $locationName . '%');
                        })->orWhereHas('county', function ($q) use ($locationName) {
                            $q->where('name', 'like',  $locationName . '%');
                        });
                        });
                    }


                    if (!is_null($request->query('profileName'))) {
                        $region_data->whereHas('escort.profile', function ($query) use ($request) {
                        $query->where('name', $request->query('profileName'));
                    })->orWhereHas('escort', function ($query) use ($request) {
                        $query->where('username', $request->query('profileName'));
                    });
                    }

                    if(!is_null($request->query('locationName'))){
                    $locationName = $request->query('locationName');

                    $region_data->whereHas('escort.profile', function ($query) use ($locationName) {
                        $query->whereHas('city', function ($q) use ($locationName) {
                            $q->where('name', 'like',  $locationName . '%');
                        })->orWhereHas('region', function ($q) use ($locationName) {
                            $q->where('name', 'like',  $locationName . '%');
                        })->orWhereHas('county', function ($q) use ($locationName) {
                            $q->where('name', 'like',  $locationName . '%');
                        });
                        });
                    }

                    if (!is_null($request->query('ethnicity'))) {
                        $region_data->whereHas('escort.profile', function ($query) use ($request) {
                            $query->where('ethnicity', $request->query('ethnicity'));
                        });
                    }
        
                    if (!is_null($request->query('cock_size'))) {
                        $region_data->whereHas('escort.profile', function ($query) use ($request) {
                            $query->where('cock_size', $request->query('cock_size'));
                        });
                    }
                    if (!is_null($request->query('orientation'))) {
                        $region_data->whereHas('escort.profile', function ($query) use ($request) {
                            $query->where('orientation', $request->query('orientation'));
                        });
                    }

                $region_data = $region_data->join(
                    \DB::raw($rawSubQuary),
                    'subscriptions.id',
                    '=',
                    'latest_subscription.max_id'
                )
                    ->whereColumn('subscriptions.id', '=', 'latest_subscription.max_id')
                    ->selectRaw('COUNT(DISTINCT subscriptions.escort_id) as subscription_count')
                    ->first();

                // Check if `extra_location` refers to the city and adjust counts for county and region
                $county_in_extra_location = EscortSubscription::whereRaw('
                JSON_CONTAINS(extra_location, CAST(? AS CHAR))', [$location->id])
                    ->where('subscriptions.end_date', '>', now())
                    ->where('subscriptions.is_hidden', 0)
                    ->exists();

                if ($county_in_extra_location) {

                    // $region_data->subscription_count += $county_data->subscription_count;
                }

                $location['subscription_count'] = $county_data->subscription_count;
                $region['subscription_count'] = $region_data->subscription_count;

                return Resp::success(['location_type' => $location->type, 'data' => ['county' => $location, 'region' => $region]]);

            } else {
                $region = $location;
            }
        }

        $region_data = EscortSubscription::join('profile', 'subscriptions.escort_id', '=', 'profile.escort_id')
            ->where('subscriptions.end_date', '>', now())
            ->where('subscriptions.is_hidden', 0)
            ->where(function ($query) use ($location) {
                $query->where('profile.region_id', $location->id)
                    ->orWhereRaw('JSON_CONTAINS(subscriptions.extra_location, CAST(? AS CHAR))', [$location->id])
                    ->orWhereHas('extraLocations', function ($query) use ($location) {
                        $query->where('region_id', $location->id);
                    });
            })->whereHas('escort.profile', function($query) {
                $query->where('verified_status', 1);
            });

            //return Resp::success(["data" => $region_data]);


            if (!is_null($request->query('rate'))) {
                $region_data->whereHas('escort.profile.rates', function ($query) use ($request) {
                    // Check for the selected rate type (e.g., '15_min', '30_min', etc.)


                    // Check for a specific rate value
                    if ($request->query('specific_rate')) {
                        $query->where('' . $request->query('rate_type') . '', "<=", $request->query('specific_rate'));
                    }

                    // Check for Incall or Outcall options
                    if ($request->query('incall') || $request->query('outcall')) {
                        $query->where(function ($q) use ($request) {
                            if ($request->query('incall')) {
                                $q->orWhere('category', 'Incall');
                            }
                            if ($request->query('outcall')) {
                                $q->orWhere('category', 'Outcall');
                            }
                        });
                    }
                });
            }

            if (!is_null($request->query('profileName'))) {
                $region_data->whereHas('escort.profile', function ($query) use ($request) {
                    $query->where('name', 'like', $request->query('profileName') . '%');
                })->orWhereHas('escort', function ($query) use ($request) {
                    $query->where('username', 'like', $request->query('profileName') . '%');
                });
            }


            if(!is_null($request->query('locationName'))){
                    $locationName = $request->query('locationName');

                    $region_data->whereHas('escort.profile', function ($query) use ($locationName) {
                        $query->whereHas('city', function ($q) use ($locationName) {
                            $q->where('name', 'like',  $locationName . '%');
                        })->orWhereHas('region', function ($q) use ($locationName) {
                            $q->where('name', 'like',  $locationName . '%');
                        })->orWhereHas('county', function ($q) use ($locationName) {
                            $q->where('name', 'like',  $locationName . '%');
                        });
                        });
            }

            


        


            if (!is_null($request->query('plan_code'))) {
                $region_data->where('plan_code', $request->query('plan_code'));
            }

            if (!is_null($request->query('ethnicity'))) {
                $region_data->whereHas('escort.profile', function ($query) use ($request) {
                    $query->where('ethnicity', $request->query('ethnicity'));
                });
            }

            if (!is_null($request->query('cock_size'))) {
                $region_data->whereHas('escort.profile', function ($query) use ($request) {
                    $query->where('cock_size', $request->query('cock_size'));
                });
            }
            if (!is_null($request->query('orientation'))) {
                $region_data->whereHas('escort.profile', function ($query) use ($request) {
                    $query->where('orientation', $request->query('orientation'));
                });
            }

        $region_data = $region_data->join(
            \DB::raw($rawSubQuary),
            'subscriptions.id',
            '=',
            'latest_subscription.max_id'
        )
            ->whereColumn('subscriptions.id', '=', 'latest_subscription.max_id')
            ->selectRaw('COUNT(DISTINCT subscriptions.escort_id) as subscription_count')
            ->first();

        $region['subscription_count'] = $region_data->subscription_count;

        return Resp::success(['location_type' => $location->type, 'data' => ['region' => $region]]);
    }






    public function getTSWeekSubscriptions(Request $request)
    {
        try {
            $user = auth()->user();
            $subscriptions = EscortSubscription::query();

            $subscriptions->leftJoin('plans', 'subscriptions.plan_code', '=', 'plans.code')
                ->select('subscriptions.*', 'plans.title as plan_title')
                ->where('subscriptions.end_date', '>', now())
                ->where('subscriptions.is_hidden', 0)
                ->where('subscriptions.plan_code',"P101")
                ->where('subscriptions.status','ACTIVE');

            $result = $subscriptions->with([
                'escort',
                // 'escort.profile.county',
                // 'escort.profile.city',
                // 'escort.profile.region',
                // 'escort.profile.reviews',
                // 'escort.profile.media',
                'escort.profile.rates',
                'orders',
                'media'
            ])->get();

            return Resp::success([
                "list" => $result,
                ]);
        } catch (\Exception $e) {
            return Resp::error(['message' => 'Something went wrong' . $e->getMessage()]);
        }
    }
}
