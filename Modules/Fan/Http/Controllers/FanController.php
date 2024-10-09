<?php

namespace Modules\Fan\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Auth\app\Http\Middleware\AuthMiddleware;
use Illuminate\Support\Facades\Log;
use Modules\Escort\app\Models\EscortReviews;
use Modules\Users\Entities\User;
use Illuminate\Support\Facades\Validator;
class FanController extends Controller
{
    public function __construct()
    {
        $this->middleware(AuthMiddleware::class);
    }

    public function getUsers(Request $request){
        // Fetch user_ids from Reviews table
        Log::info("Review Controller here getUsers");
        $userIds = EscortReviews::pluck('user_id')->unique();
        $users = User::whereIn('id', $userIds)->get();
        if ($users->isEmpty()) {
            return response()->json(['message' => 'No users found with reviews'], 404);
        }

        return response()->json(['users' => $users], 200);
    }
    public function create(Request $request)

    {
        //$user=$request->user;
        $user=auth()->user();
        Log::info("Review Controller here create function");
        Validator::make($request->all(), [
            'photo_accuracy' => 'nullable|integer',
            'service' => 'nullable|integer',
            'clean_liness' => 'nullable|integer',
            'location' => 'nullable|integer',
            'value_for_money' => 'nullable|integer',
            'comments' => 'required|string',
            'escort_id' => 'required|integer',
        ]);
        Log::info("Validation passed");
        if (EscortReviews::where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'You have already submitted a review.'], 409);
        }

        Log::info("Review Controller here----------------------------------------------------");
        $review = EscortReviews::create([
            'user_id' => $user->id,
            'photo_accuracy' => $request->photo_accuracy,
            'service' => $request->service,
            'clean_liness' => $request->clean_liness,
            'location' => $request->location,
            'value_for_money' => $request->value_for_money,
            'comments' => $request->comments,
            'escort_id' => $request->escort_id,
        ]);
        //$review = EscortReviews::create($request->all());
        return response()->json($review, 201);
    }
  
}
