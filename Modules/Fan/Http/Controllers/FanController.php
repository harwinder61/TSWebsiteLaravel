<?php

namespace Modules\Fan\Http\Controllers;

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

class FanController extends Controller
{

    public function __construct()
    {
        $this->middleware(AuthMiddleware::class);
    }

    public function find(Request $request)
    {
        $user = auth()->user();
        $reviews = FanReviews::where('user_id', $user->id)->get();
        $reviews->load('escort');
        $totalRating = 0;
        foreach ($reviews as $review) {
            $totalRating += $review->photo_accuracy + $review->service + $review->clean_liness + $review->location + $review->value_for_money;
        }
        
        $averageRating = $reviews->count() > 0 ? $totalRating / (5 * $reviews->count()) : 0;

        return Resp::success([
            'list' => $reviews,
            'average_rating' => round($averageRating, 2)
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
            return Resp::error(['You have already submitted a review']);
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

        return Resp::success([$review]);
    }
}
