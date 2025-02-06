<?php

namespace Modules\Fan\Http\Controllers;

use App\Models\Media as ModelsMedia;
use App\Services\Resp;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Auth\app\Http\Middleware\AuthMiddleware;
use Illuminate\Support\Facades\Log;
use Modules\Escort\app\Models\EscortReviews;
use Modules\Fan\app\Models\FanReviews;
use Modules\Auth\app\Models\AuthUser;
use Illuminate\Support\Facades\Validator;
use Modules\Escort\app\Models\EscortSubscription;
use Modules\Fan\app\Models\ProfileLike;
use Illuminate\Support\Facades\Hash;
use Modules\Admin\app\Models\Blog;
use Modules\Fan\app\Models\Fan;
use App\Models\Media;
use Modules\Admin\app\Models\Pages;
use Modules\Fan\app\Models\FanSubscription;
use Modules\Escort\app\Models\Profile;
use Modules\Fan\app\Models\ProfileRates;
use Modules\Admin\app\Models\Setting;
use App\Notifications\ReviewSubmitted;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailHelper;


class FanController extends Controller
{

    public function __construct()
    {
        $this->middleware(AuthMiddleware::class)->except('blog', 'allBlogList', 'getDynamicPagesList','getMultipleProfiles');
    }
    public function create(Request $request)
    {
        $user = auth()->user();

        Validator::make($request->all(), [
            'photo_accuracy' => 'nullable|integer',
            'service' => 'nullable|integer',
            'clean_liness' => 'nullable|integer',
            'location' => 'nullable|integer',
            'value_for_money' => 'nullable|integer',
            'comment' => 'required|string',
            'escort_id' => 'required|integer',
        ]);

        if (EscortReviews::where('user_id', $user->id)->where('escort_id', $request->escort_id)->exists()) {
            return Resp::success(['Error' => 'You have already submitted a review for this escort']);
        }
        $escort_exists = AuthUser::find($request->escort_id);
        if (!$escort_exists) {
            return Resp::error(['Escort user not found']);
        }
        if ($escort_exists->user_type != 2) {
            return Resp::error(["This user is not an escort"]);
        }

        $review = EscortReviews::create([
            'user_id' => $user->id,
            'photo_accuracy' => $request->photo_accuracy,
            'service' => $request->service,
            'clean_liness' => $request->clean_liness,
            'location' => $request->location,
            'value_for_money' => $request->value_for_money,
            'comment' => $request->comment,
            'escort_id' => $request->escort_id,
        ]);
        
        $escort_exists = AuthUser::find($request->escort_id);
        if (!$escort_exists) {
            return Resp::error(['Escort user not found']);
        }
        $fan_exists = AuthUser::find($user->id);
        if (!$fan_exists) {
            return Resp::error(['Fan user not found']);
        }
    
        $dynamicData = [
            '[ESCORT_NAME]' => $escort_exists->username,
            '[fAN_NAME]' => $fan_exists->username,
        
        ];
        try {
            EmailHelper::sendDynamicEmail(
                'a_new_review_added',
                $dynamicData,
                env('ADMIN_EMAIL')
            );
            Log::info('Verification email sent to: ' . $user->email);

        } catch (\Exception $e) {
            Log::error('Failed to send verification email to ' . $user->email . ': ' . $e->getMessage());
        }


    
        return Resp::success(['message' => 'Review created successfully', 'review' => $review]);
        $review->save();
    }


//     public function create(Request $request)
// {
//     $user = auth()->user();

//     Validator::make($request->all(), [
//         'photo_accuracy' => 'nullable|integer',
//         'service' => 'nullable|integer',
//         'clean_liness' => 'nullable|integer',
//         'location' => 'nullable|integer',
//         'value_for_money' => 'nullable|integer',
//         'comment' => 'required|string',
//         'escort_name' => 'required|string',
//         'fan_name' => 'required|string',
//     ]);

//     // Get escort by name
  

//     if (!$escort_exists) {
//         return Resp::error(['Escort user not found']);
//     }

//     if ($escort_exists->user_type != 2) {
//         return Resp::error(["This user is not an escort"]);
//     }

//     // Check if the user has already submitted a review for this escort
//     if (EscortReviews::where('user_id', $user->id)->where('escort_id', $escort_exists->id)->exists()) {
//         return Resp::success(['Error' => 'You have already submitted a review for this escort']);
//     }

//     $review = EscortReviews::create([
//         'user_id' => $user->id,
//         'photo_accuracy' => $request->photo_accuracy,
//         'service' => $request->service,
//         'clean_liness' => $request->clean_liness,
//         'location' => $request->location,
//         'value_for_money' => $request->value_for_money,
//         'comment' => $request->comment,
//         'escort_id' => $escort_exists->id,
//     ]);

//     // Prepare dynamic data for the email
//     $dynamicData = [
//         '[ESCORT_NAME]' => $escort_exists->username,
//         '[FAN_NAME]' => $fan_exists->username,
//     ];

//     try {
//         // Send the email notification
//         EmailHelper::sendDynamicEmail(
//             'ts_fan_review_submitted',
//             $dynamicData,
//             env('ADMIN_EMAIL')
//         );
//         Log::info('Review submission email sent to: ' . env('ADMIN_EMAIL'));

//     } catch (\Exception $e) {
//         Log::error('Failed to send review submission email: ' . $e->getMessage());
//     }

//     return Resp::success(['message' => 'Review created successfully', 'review' => $review]);
// }




    public function getDynamicPagesList(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);
        $offset = ($page - 1) * $perPage;
        if (!$request->query('id')) {
            $pages = Pages::orderBy('created_at', 'desc') // Order by created_at in descending order
                ->offset($offset)
                ->limit($perPage)
                ->get();
        } else {
            $pages = Pages::find($request->query('id'));
        }
        if (!$pages) {
            return Resp::error(['message' => 'Page not found']);
        }
        $pages->load('media');
        $total_results = Pages::count();
        $total_pages = ceil($total_results / $perPage);
        $pagination = [
            'total_results' => $total_results,
            'total_pages' => $total_pages,
            'page' => $page,
            'page_size' => $perPage
        ];
        return Resp::success(['pages' => $pages, 'pagination' => $pagination]);
    }

    public function allBlogList(Request $request)
    {
        // Get pagination parameters
        $perPage = $request->query('per_page', 11);
        $page = $request->query('page', 1);
        $offset = ($page - 1) * $perPage;
        $blogs = Blog::with('media')
            ->orderBy('created_at', 'desc')
            ->skip($offset)
            ->take($perPage);
        if (!is_null($request->query('status'))) {
            $blogs->where('status', $request->query('status'));
        }
        if (!is_null($request->query('s'))) {
            $search_term = $request->query('s');
            $blogs->where('title', 'like', '%' . $search_term . '%');
        }
        if (!is_null($request->query('title'))) {
            $search_term = $request->query('title');
            $blogs->where('title', 'like', '%' . $search_term . '%');
        }


        $blogs = $blogs->get();
        $total_results = Blog::count();
        $total_pages = ceil($total_results / $perPage);
        $pagination = [
            'total_results' => $total_results,
            'total_pages' => $total_pages,
            'page' => (int)$page,
            'page_size' => $perPage
        ];
        $randomBlogs = Blog::with('media')->inRandomOrder()->take(2)->get();
        return Resp::success([
            'list' => $blogs,
            'pagination' => $pagination,
            'random' => $randomBlogs
        ]);
    }



    public function blog(Request $request)
    {
        $blogs = Blog::with('media')->orderBy('created_at', 'desc')->get();
        return Resp::success(['list' => $blogs]);
    }


    public function changeUsername(Request $request)
    {
        $request->validate([
            'old_username' => 'required|string',
            'new_username' => 'required|string|unique:users,username,' . auth()->user()->id,
            'confirm_username' => 'required|string'
        ]);

        $user = auth()->user();
        if ($user->username != $request->old_username) {
            return Resp::error(['Invalid old username']);
        }
        if ($request->new_username != $request->confirm_username) {
            return Resp::error(['New username and confirm username do not match']);
        }

        $user->username = $request->new_username;
        $user->save();

        return Resp::success(['message' => 'Username updated successfully']);
    }
    public function likeProfile(Request $request)
    {
        $user = auth()->user();
        $escort_id = $request->escort_id;
        $is_like = $request->is_like ? 1 : 0;
        $existingLike = ProfileLike::where('fan_id', $user->id)->where('escort_id', $escort_id)->first();
        if ($existingLike) {
            $existingLike->update(['is_like' => $is_like]);
        } else {
            ProfileLike::create(['fan_id' => $user->id, 'escort_id' => $escort_id, 'is_like' => $is_like]);
        }
        return Resp::success(['message' => $is_like ? 'Profile liked ' : 'Profile unliked']);
    }

    public function find(Request $request)
    {
        $user = auth()->user();

        // Get pagination parameters
        $perPage = $request->query('per_page', 20);
        $page = $request->query('page', 1);
        $offset = ($page - 1) * $perPage;

        // Retrieve paginated reviews
        $reviews = FanReviews::where('user_id', $user->id)
            ->with('profile.media') // eager load the media associated with each review
            ->skip($offset) // offset
            ->take($perPage) // limit the number of records per page
            ->get();


        foreach ($reviews as $review) {
            $review->avg_rating = ($review->photo_accuracy + $review->service + $review->clean_liness + $review->location + $review->value_for_money) / 5;
        }

        // Get total number of reviews for pagination
        $total_results = FanReviews::where('user_id', $user->id)->count();
        $total_pages = ceil($total_results / $perPage);
        $pagination = [
            'total_results' => $total_results,
            'total_pages' => $total_pages,
            'page_number' => $page,
            'page_size' => $perPage
        ];

        return Resp::success([
            'list' => $reviews,
            'pagination' => $pagination
        ]);
    }


    public function find_escort_reviews($id)
    {
        $user = auth()->user();
        $escort_id = $id;
        $reviews = FanReviews::where('user_id', $user->id)->where('escort_id', $escort_id)->get();
        $reviews->load('escort');
        if ($reviews->isEmpty()) {
            return Resp::error(['No reviews found']);
        }
        return Resp::success(['details' => $reviews]);
    }

    public function getUsers(Request $request)
    {
        // Fetch user_ids from Reviews table

        $userIds = EscortReviews::pluck('user_id')->unique();
        $users = AuthUser::whereIn('id', $userIds)->get();
        if ($users->isEmpty()) {
            return response()->json(['message' => 'No users found with reviews'], 404);
        }

        return response()->json(['users' => $users], 200);
    }

    public function getMultipleProfiles(Request $request){
        $validator = Validator::make($request->all(), [
          'ids' => 'required|array',
        ]);
        if($validator->fails()){
            return Resp::error(['message' => $validator->errors()]);
        }
        try{
            $ids=$request->input('ids');
            $data=Profile::with(['media','city','county','region','reviews','rates'])->whereIn('escort_id',$ids)->get();
            if(!$data){
                return Resp::error(['message' => 'No profiles found']);

            }


            foreach ($data as $profile){
                $reviews = $profile->reviews?? [] ;
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


                    $profile->avg_rating = round($averageRating, 2);
                }else{
                    $profile->avg_rating = 0;
                }
            }
            return Resp::success(['data'=>$data]);
            

        }catch(\Exception $e){}
            return Resp::error(['message' => $e->getMessage()]);
        }
}
