<?php

namespace Modules\Escort\Http\Controllers;
use Modules\Escort\app\Models\EscortReviews;
use Modules\Review\app\Models\Review;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Modules\Users\Entities\User;
use Modules\Auth\app\Http\Middleware\AuthMiddleware;

class ReviewsController extends Controller
{
    public function __construct()
    {
        $this->middleware(AuthMiddleware::class);
    }

    
    public function index(Request $request)
    {
       $productId = $request->product_id;

       Log::info("Review Controller here $productId");

        return Response::json(['message' => 'Review fetched successfully', 'reasponse' => "test successfully"], 200);
    }

    public function store(Request $request)

    {
        //$user=$request->user;
        $user=auth()->user();
        Log::info("Review Controller here $user");
        $request->validate([
            'photo_accuracy' => 'nullable|integer',
            'service' => 'nullable|integer',
            'clean_liness' => 'nullable|integer',
            'location' => 'nullable|integer',
            'value_for_money' => 'nullable|integer',
            'comment' => 'nullable|string',
            'escort_id' => 'required|integer',
        ]);
    
        // Check if a review from the same user already exists
        if (EscortReviews::where('user_id', $user->id)->exists()) {
            return response()->json(['error' => 'You have already submitted a review.'], 409);
        }


        // if(!User::where('id', $request->escort_id)->exists()){
        //  return response()->json(['error' => 'Escort user not found.'], 404);
        // }
        
        Log::info("Review Controller here----------------------------------------------------",);
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
        //$review = EscortReviews::create($request->all());
        return response()->json($review, 201);
    }
       
}
