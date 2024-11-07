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
use App\Services\Resp;

class ReviewsController extends Controller
{
    public function __construct()
    {
        
    }

    
    public function list(Request $request)
    {
                
       $reviews=EscortReviews::with('fan')->get();
        return Resp::success(['list'=>$reviews]);

    }

    
       
}
