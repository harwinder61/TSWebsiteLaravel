<?php

namespace Modules\Fan\Http\Controllers;

use App\Services\Resp;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Auth\app\Http\Middleware\AuthMiddleware;
use Illuminate\Support\Facades\Log;
use Modules\Escort\app\Models\EscortReviews;
use Modules\Fan\app\Models\FanReviews;
use Modules\Escort\app\Models\Subscription;
use Modules\Auth\Entities\User;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        $this->middleware(AuthMiddleware::class);
    }

    function getSubscriptions() {
        $user = auth()->user();
        $subscriptions = Subscription::where('escort_id', $user->id)->get();
        $subscriptions->load('escort');
        $subscriptions->load('plan');
        return Resp::success(["list" => $subscriptions]);
    }
    

    function locations() {
        $locations = Subscription::select('users.location_id as location', \DB::raw('COUNT(*) as count'))
            ->groupBy('users.location_id ')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();
        return Resp::success(["list" => $locations]);
    }
     
}



