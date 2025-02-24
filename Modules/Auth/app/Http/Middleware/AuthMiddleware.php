<?php

namespace Modules\Auth\app\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Route;
use App\Services\Resp;

class AuthMiddleware
{                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next,$guard=null)
    {

        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return Resp::error(['User not found']);
            }
            if($guard=='admin' && $user->user_type!=3){
            
                return Resp::error(['Unauthorized user is not an admin']);
            }
            if($guard=='escort' && $user->user_type!=2){
                
                return Resp::error(['Unauthorized user is not an escort']);
            }
            if($guard=='fan' && $user->user_type!=1){
                
                return Resp::error(['Unauthorized user is not a fan']);
            }
        } catch (JWTException $e) {
            return Resp::error(['Token is invalid or expired : '.$e->getMessage()],"Token is invalid",401);
        }
   
        // Attach user to request
        $request->auth = $user;
        $request->merge(['user' => $user]);

        return $next($request);
    }
}
