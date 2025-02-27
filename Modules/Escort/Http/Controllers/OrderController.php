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
use App\Models\User;
use App\Models\ExtraLocation;






class OrderController extends Controller
{
        public function __construct() {}

    function createOrder(Request $request)
    {
        $user = auth()->user();
        $image_id = 0;
        if ($request->input('image_id')) {
            $image_id = $request->input('image_id');
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
            return Resp::success(['error' => 'Plan not found']);
        }


        $sub_exists = Subscription::where('escort_id', $user->id)->first();
        $days = $plan->days;
        $end_date = date('Y-m-d', strtotime($request->input('start_date') . " + $days days"));
        if ($request->input('plan_code') == 'P101') {
            $end_date = date('Y-m-d', strtotime($end_date . " -1 day")); // Subtract one day for p101 plan_code
        }

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

        // $total_orders_count = $subscription_count + $pending_orders_count;

        $total_orders_count = $subscription_count;
        if ($total_orders_count >= $plan->advert_spaces) {
            return Resp::error(['error' => 'Max subscription reached, plan not available', 'total_subscription' => $total_orders_count]);
        }

        $start_date2 = Carbon::parse($request->input('start_date'));
        $end_date2 = Carbon::parse($end_date);

        $weekly_sub_exists = null;
        if ($request->input('plan_code') == "P101") {
            $weekly_sub_exists = Subscription::where('plan_code', $request->input('plan_code'))
                ->where('status', 'ACTIVE')
                ->where('end_date', '>', now())
                ->where(function ($query) use ($start_date2, $end_date2) {
                    $query->where(function ($q) use ($start_date2, $end_date2) {
                        $q->where('start_date', '<', $end_date2)
                            ->where('end_date', '>', $start_date2);
                    })
                        ->orWhere(function ($q) use ($start_date2, $end_date2) {
                            $q->whereBetween('start_date', [$start_date2, $end_date2]);
                        });
                })->exists();
                // ->get();
                // if ($weekly_sub_exists->isNotEmpty())
            if ($weekly_sub_exists) {
                return Resp::error([
                'error' => 'Weekly subscription is already owned by someone',
                'payload'=>[
                    'start_date'=>$start_date2,
                    'end_date'=>$end_date2
                ]]);
            }
        }

        $plan = Plan::where('code', $request->input('plan_code'))->first();
        if (!$plan) {
            return Resp::error(['error' => 'Plan not found']);
        }

        $extra_locations = $request->input('extra_locations'); // Assuming this is an array
        $extra_location_count = is_array($extra_locations) ? count($extra_locations) : 0;

        // Calculate the total price
        $total_price = 0;



        if ($request->input('plan_code') == "P105" && !$sub_exists) {
            $total_price = 0;
        } else {
            $total_price = $plan->price + (2 * $extra_location_count);
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
            'price' => $total_price,

        ]);

        if (!$order) {
            return Resp::error(['error' => 'Failed to create order']);
        }
        session(['order_id' => $order->id]);
        Log::info("Session data order_id: " . session('order_id'));

        // Stripe payment logic remains the same
        $session_url = "";
        try {
            // Set the Stripe secret key
            Stripe::setVerifySslCerts(false);
            Stripe::setApiKey(env('STRIPE_SECRET'));

            $plan = Plan::where('code', $request->input('plan_code'))->first();
            $extra_locations_price = 0;
            $price = $plan->price;
            if ($request->input('plan_code') == "P105" && !$sub_exists) {
                $price = 0;
                EmailHelper::sendDynamicEmail(
                    'ts_great_news_you_are_step_away_to_place_your_free_featured_ad',
                    ['[USER_LOGIN]' => $user->username, '[UNCORAGING_URL]' => env('APP_URL') . '/booking/P105'],
                    $user->email
                );
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
            'order_id' => $order->id,
            'client_secret' => $paymentIntent->client_secret,
            'dpmCheckerLink' => "https://dashboard.stripe.com/settings/payment_methods/review?transaction_id={$paymentIntent->id}",
        ];

        // ... existing code ...

        $media = Media::where('id', $request->input('image_id'))
            ->where('escort_id', $user->id)
            ->first();

        if (!$media || $media->id != $request->input('image_id')) {
            return Resp::success(['error' => 'Invalid image id']);
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

        // EmailHelper::sendDynamicEmail(
        //     'new_order',
        //     [
        //         '[CUSTOMER_NAME]' => $user->username,
        //         '[PLAN_CODE]' => session('plan_code'),
        //         '[START_DATE]' => session('start_date'),
        //         '[END_DATE]' => session('end_date'),
        //         '[PRICE]' => session('price'),
        //         '[TOTAL]' => session('total'),
        //         '[PLAN_TITLE]' => session('plan_title'),
        //         '[ORDER_ID]' => session('order_id'),
        //         '[USER_EMAIL]' => $user->email

        //     ],
        //     $user->email
        // );
        return Resp::success($response);
    }



    function updateOrder(Request $request)
    {
        $user = auth()->user();
        $order_id = $request->input('order_id');
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|string|exists:orders,id',
        ]);
        if ($validator->fails()) {
            return Resp::error([$validator->errors()]);
        }
        $order = Orders::find($order_id);
        if (!$order) {
            return Resp::error(['Order not found']);
        }

        $plan = Plan::where('code', $request->input('plan_code'))->first();
        if (!$plan) {
            return Resp::error(['Plan not found']);
        }

        $days = $plan->days;
        $end_date = date('Y-m-d', strtotime($request->input('start_date') . " + $days days"));

        $updated_order = Orders::where('id', $order_id)->update([
            'image_id' => $request->input('image_id'),
            'start_date' => $request->input('start_date'),
            'end_date' => $end_date,
            'only_fans_link' => $request->input('only_fans_link') ?? '',
            'many_vids_link' => $request->input('many_vids_link') ?? '',
            'fan_centro_link' => $request->input('fan_centro_link') ?? '',
        ]);
        if (!$updated_order) {
            return Resp::error(['Failed to update order']);
        }
        return Resp::success(['Order updated successfully']);
    }
    function webhook_payment_status_update(Request $request)
    {
        $user = auth()->user();
        $order_id = $request->input('order_id');
        $extra_location = $request->input('extra_locations');
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|string|exists:orders,id',
            'extra_locations' => 'nullable|array',
            'extra_locations.*' => 'exists:locations,id'
        ]);
        if ($validator->fails()) {
            return Resp::error([$validator->errors()]);
        }
        $order = Orders::find($order_id);

        if (!$order) {
            return Resp::error(['error' => 'Order not found']);
        }
        if ($order->payment_status == "PENDING") {
            $order->update([
                'payment_status' => 'PAID',
            ]);
            // $escort = AuthUser::find($order->escort_id);
            // $email = new EmailService();
            // $email->to($escort->email);
            // $email->subject('Thanku for purchasing subscription hope you enjoy our services');
            // $email->setBodyPurchasingEmail('purchasing', ['user' => $order->escort_id]);
            // $email->send();

            $plan = Plan::where('code', $order->plan_code)->first();
            if (!$plan) {
                return Resp::error(['error' => 'Plan not found']);
            }
            $days = $plan->days;


            //$subscription_exists=Subscription::where('escort_id',$order->escort_id)
            //    ->where('plan_code',$order->plan_code)
            //    ->where('status','ACTIVE')
            //    ->first();


            //if($subscription_exists){
            //    return Resp::error(['Subscription already exists']);
            //}
            Log::info("Number of days from plans table : " . $days);
            // Ensure the start_date is in the correct format
            // Ensure the start_date is in the correct format
            $start_date = Carbon::parse($order->start_date);
            $end_date = $start_date->addDays($days)->subDay()->format('Y-m-d'); // Subtract one day from the calculated end date
            //old end_date calculation
            //$end_date = $start_date->addDays($days)->format('Y-m-d'); // Correctly calculate end date

            Log::info("End date : " . $end_date);
            Log::info("Start date : " . $order->start_date);
            $subscription = Subscription::create([
                'escort_id' => $order->escort_id,
                'order_id' => $order->id,
                'plan_code' => $order->plan_code,
                'start_date' => $order->start_date,
                'image_id' => $order->image_id,
                'status' => 'ACTIVE',
                'end_date' => $end_date,
                'extra_location' => $extra_location
            ]);

            // Assuming you receive an array of IDs from the request
            $locationIds = $extra_location ?? []; // e.g., [1, 2, 3]

            foreach ($locationIds as $locationId) {
                // Fetch the location from the database
                $location = Location::find($locationId); // Assuming you have a Location model

                if ($location) {
                    // Initialize variables for region, county, and city IDs
                    $regionId = null;
                    $countyId = null;
                    $cityId = null;

                    // Determine the type of location and fetch the appropriate IDs
                    if ($location->type === 'region') {
                        $regionId = $location->id;
                    } elseif ($location->type === 'county') {
                        $countyId = $location->id;
                        $regionId = $location->parent_id; // Assuming parent_id is the region ID
                    } elseif ($location->type === 'city') {
                        $cityId = $location->id;
                        $countyId = $location->parent_id; // Assuming parent_id is the county ID
                        $regionId = Location::find($countyId)->parent_id; // Fetch the region ID from the county
                    }

                    // Check for existing entry to avoid duplication
                    $existing = ExtraLocation::where('subscription_id', $subscription->id)
                        ->where(function ($query) use ($regionId, $countyId, $cityId) {
                            if ($regionId) {
                                $query->where('region_id', $regionId);
                            }
                            if ($countyId) {
                                $query->where('county_id', $countyId);
                            }
                            if ($cityId) {
                                $query->where('city_id', $cityId);
                            }
                        })
                        ->first();

                    // If no existing entry, create a new one
                    if (!$existing) {
                        ExtraLocation::create([
                            'subscription_id' => $subscription->id, // Assuming $subscription is defined
                            'region_id' => $regionId,
                            'county_id' => $countyId,
                            'city_id' => $cityId,
                        ]);
                    }
                }
            }

            EmailHelper::sendDynamicEmail(
                'ts_new_order_notification',
                [
                    '[CUSTOMER_NAME]' => $user->username,
                    '[PLAN_CODE]' => $plan->code,
                    '[START_DATE]' => Carbon::parse($order->start_date)->format('Y-m-d'),
                    '[END_DATE]' => Carbon::parse($end_date)->format('Y-m-d'),
                    '[PRICE]' => $plan->price,
                    '[TOTAL]' => $plan->price,
                    '[PLAN_TITLE]' => $plan->title,
                    '[ORDER_ID]' => $order->id,
                    '[USER_EMAIL]' => $user->email,
                    '[SUBSCRIPTION_ID]' => $subscription->id

                ],
                $user->email
            );




            if (!$subscription) {
                return Resp::error(['error' => 'Failed to create subscription']);
            }
            return Resp::success(['subscription' => $subscription, 'order' => $order]);
        } else {
            return Resp::error(['error' => 'Order already paid']);
        }
        return Resp::success(['order' => $order]);
    }

    function getSubscription()
    {
        $user = auth()->user();

        $subscription = Subscription::where('escort_id', $user->id)
            ->where('status', 'ACTIVE')
            ->get();
        return Resp::success(["list" => $subscription]);
    }

    function getLocationAndSubscriptions(Request $request)
    {
        if (!$request->query('s')) {
            return Resp::success(["locations" => [], "subscriptions" => []]);
        }
        $search = $request->query('s');
        $location = Location::with('county')->where('name', 'LIKE', '%' . $search . '%')->get();
        // $subscriptions=Subscription::query();
        $subscriptions = Subscription::with('escort.profile', 'media', 'escort.profile.city', 'escort.profile.region', 'escort.profile.county')->whereHas('escort.profile', function ($query) use ($search) {
            $query->where('name', 'LIKE', '%' . $search . '%')
            ->where('subscriptions.end_date', '>', now())
            ->where('subscriptions.is_hidden', 0);
        })->get();


        $byPlanOrder = filter_var($request->query('byPlanOrder'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($byPlanOrder) {
                // $rawSubQuary = '(SELECT escort_id, MAX(end_date) as latest_end_date, MAX(id)
                // as max_id FROM subscriptions GROUP BY escort_id) as latest_subscription';
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


            //  $subscriptions->join(
            //     \DB::raw($rawSubQuary),
            //     'subscriptions.id',
            //     '=',
            //     'latest_subscription.max_id'

            // )
            //     ->whereColumn('subscriptions.id', '=', 'latest_subscription.max_id');



            

        return Resp::success(["locations" => $location, "subscriptions" => $subscriptions]);
    }
    public function getEscortPreviousSubscriptions(Request $request)
    {
        $user = auth()->user();
        $subscriptions = Subscription::where('escort_id', $user->id)
            ->where('status', 'ACTIVE')
            ->where('plan_code', 'P101')
            ->where('start_date', '>', date('Y-m-d'))
            ->get();
        return Resp::success(['subscriptions' => $subscriptions]);
    }

    public function getLatestEscortSubscription(Request $request)
    {
        $user = auth()->user();
        $subscription = Subscription::with('orders')->where('escort_id', $user->id)->orderBy('id', 'desc')->first();
        if (!$subscription) {
            return Resp::error(['Subscription not found']);
        }
        return Resp::success(['data' => $subscription]);
    }

    public function updateLatestEscortSubscription(Request $request)
    {
        $user = auth()->user();
        $sub_id = $request->input('subscription_id');
        $subscription = Subscription::find($sub_id);

        if (!$subscription) {
            return Resp::error(['Subscription not found']);
        }


        $updateData = [];
        Log::info("start of loop");
        if ($request->has('extra_locations')) {
            $updated_locations = $subscription->update(['extra_location' => $request->input('extra_locations')]);
            if (!$updated_locations) {
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

        if (!$updated_subscription) {
            return Resp::error(['Failed to update subscription']);
        }


        return Resp::success(['data' => $subscription]);
    }


    // public function updateLatestEscortSubscription(Request $request)
    // {
    //     // Get the authenticated user and subscription ID
    //     $user = auth()->user();
    //     $sub_id = $request->input('subscription_id');
    //     $subscription = Subscription::find($sub_id);

    //     // Check if the subscription exists
    //     if (!$subscription) {
    //         return Resp::error(['Subscription not found']);
    //     }

    //     // Update other fields if provided
    //     $updateData = [];
    //     if ($request->has('onlyfans_link')) {
    //         $updateData['only_fans_link'] = $request->input('onlyfans_link');
    //     }
    //     if ($request->has('manyvids_link')) {
    //         $updateData['many_vids_link'] = $request->input('manyvids_link');
    //     }
    //     if ($request->has('fancentro_link')) {
    //         $updateData['fan_centro_link'] = $request->input('fancentro_link');
    //     }

    //     // Update subscription order fields
    //     $subscription->orders->update($updateData);

    //     // Handle extra locations
    //     if ($request->has('extra_locations')) {
    //         $locationIds = $request->input('extra_locations');

    //         // Fetch existing extra locations for the subscription
    //         $existingExtraLocations = ExtraLocation::where('subscription_id', $subscription->id)->get();

    //         // Create a set of existing IDs for quick lookup
    //         $existingIds = $existingExtraLocations->pluck('id')->toArray();

    //         foreach ($locationIds as $locationId) {
    //             $location = Location::find($locationId);

    //             if ($location) {
    //                 // Determine region, county, and city IDs
    //                 $regionId = $location->type === 'region' ? $location->id : ($location->type === 'county' ? $location->parent_id : Location::find($location->parent_id)->parent_id);
    //                 $countyId = $location->type === 'county' ? $location->id : ($location->type === 'city' ? $location->parent_id : null);
    //                 $cityId = $location->type === 'city' ? $location->id : null;

    //                 // Check for existing entry
    //                 $existing = ExtraLocation::where('subscription_id', $subscription->id)
    //                     ->where('region_id', $regionId)
    //                     ->where('county_id', $countyId)
    //                     ->where('city_id', $cityId)
    //                     ->first();

    //                 // Create or update entry
    //                 if (!$existing) {
    //                     ExtraLocation::create([
    //                         'subscription_id' => $subscription->id,
    //                         'region_id' => $regionId,
    //                         'county_id' => $countyId,
    //                         'city_id' => $cityId,
    //                     ]);
    //                 }
    //             }
    //         }

    //         // Remove extra locations that are no longer in the incoming request
    //         foreach ($existingExtraLocations as $extraLocation) {
    //             if (!in_array($extraLocation->city_id, $locationIds) && !in_array($extraLocation->county_id, $locationIds) && !in_array($extraLocation->region_id, $locationIds)) {
    //                 $extraLocation->delete();
    //             }
    //         }
    //     }

    //     return Resp::success(['data' => $subscription]);
    // }
    public function locationIdsToLocationNames(Request $request)
    {

        $ids = $request->input("location_ids");
        $locations = Location::whereIn('id', $ids)->get();
        return Resp::success(['locations' => $locations]);
    }

    public function extraLocationsUpdatedOrder(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'extra_locations' => 'required|array',
                'extra_locations.*' => 'exists:locations,id',
                'image_id' => 'required|exists:media,id',
                'subscription_id' => 'required|exists:subscriptions,id',

            ]);
            if ($validator->fails()) {
                return Resp::error([$validator->errors()]);
            }

            $subscription_data = Subscription::find($request->input('subscription_id'));
            if (!$subscription_data) {
                return Resp::error(['Subscription not found']);
            }

            $order = Orders::create([
                'escort_id' => $subscription_data->escort_id,
                'plan_code' => $subscription_data->plan_code,
                'start_date' => $subscription_data->start_date,
                'end_date' => $subscription_data->end_date,
                'payment_status' => "PENDING",
                'image_id' => $request->input('image_id'),
                'extra_location' => $request->input('extra_locations'),
            ]);
            if (!$order) {
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
                'image_id' => $request->input('image_id')
            ]);
            if (!$updated_subscription) {
                return Resp::error(['Failed to update subscription']);
            }
            $subscription = Subscription::find($request->input('subscription_id'));
            if (!$subscription) {
                return Resp::error(['Subscription not found']);
            }
            $locationIds = $request->input('extra_locations'); // e.g., [1, 2, 3]

            // Fetch existing extra locations for the subscription
            $existingExtraLocations = ExtraLocation::where('subscription_id', $subscription->id)->get();

            // Create a set of IDs from the incoming request
            $incomingLocationIds = collect($locationIds)->map(function ($locationId) {
                $location = Location::find($locationId); // Fetch location to determine type
                return $location ? $location->id : null; // Ensure valid ID
            })->filter(); // Filter out any null values
            Log::info("incomingLocationIds : ");
            Log::info($incomingLocationIds);

            // Insert or update extra locations
            foreach ($incomingLocationIds as $locationId) {
                $location = Location::find($locationId); // Fetch the location

                if (!$location) {
                    Log::error("Location not found for ID: $locationId");
                    continue; // Skip to the next ID
                }
                if ($location) {
                    // Initialize variables for region, county, and city IDs
                    $regionId = null;
                    $countyId = null;
                    $cityId = null;

                    // Determine the type of location and fetch the appropriate IDs
                    if ($location->type === 'region') {
                        $regionId = $location->id;
                    } elseif ($location->type === 'county') {
                        $countyId = $location->id;
                        $regionId = $location->parent_id; // Assuming parent_id is the region ID
                    } elseif ($location->type === 'city') {
                        $cityId = $location->id;
                        $countyId = $location->parent_id; // Assuming parent_id is the county ID
                        $regionId = Location::find($countyId)->parent_id; // Fetch the region ID from the county
                    }

                    // Check for existing entry to avoid duplication
                    $existing = ExtraLocation::where('subscription_id', $subscription->id)
                        ->where(function ($query) use ($regionId, $countyId, $cityId) {
                            if ($regionId) {
                                $query->where('region_id', $regionId);
                            }
                            if ($countyId) {
                                $query->where('county_id', $countyId);
                            }
                            if ($cityId) {
                                $query->where('city_id', $cityId);
                            }
                        })
                        ->first();

                    // If no existing entry, create a new one
                    if (!$existing) {
                        ExtraLocation::create([
                            'subscription_id' => $subscription->id, // Assuming $subscription is defined
                            'region_id' => $regionId,
                            'county_id' => $countyId,
                            'city_id' => $cityId,
                        ]);
                    }
                }
            }

            // Remove extra locations that are no longer in the incoming request
            foreach ($existingExtraLocations as $extraLocation) {
                if (
                    !$incomingLocationIds->contains($extraLocation->city_id) &&
                    !$incomingLocationIds->contains($extraLocation->county_id) &&
                    !$incomingLocationIds->contains($extraLocation->region_id)
                ) {
                    $extraLocation->delete(); // Delete if not present in the incoming request
                }
            }
            return Resp::success(['order' => $order]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Resp::error([$e->getMessage()]);
        }
    }

    public function createFreeSubscription(Request $request)
    {
        $user = auth()->user();
        $order = Orders::create([
            'escort_id' => $user->id,
            'plan_code' => 'P101',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+10 days')),
            'payment_status' => 'PAID',
            'image_id' => $request->input('image_id'),
            'extra_location' => $request->input('extra_locations'),
        ]);
        $subscription = Subscription::create([
            'escort_id' => $user->id,
            'plan_code' => 'P101',
            'status' => 'ACTIVE',
        ]);
    }
}
