<?php

namespace Modules\Escort\Http\Controllers;
use Modules\Escort\app\Models\EscortReviews;
use Modules\Review\app\Models\Review;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
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

        return Response::json(['message' => 'Review fetched successfully', 'reasponse' => "test successfully"], 200);
    }

 
       
}
