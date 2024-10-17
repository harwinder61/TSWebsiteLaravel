<?php

namespace Modules\Fan\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Services\Resp;


class AuthFan
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return Resp::error(['User not found']);
            }
        } catch (JWTException $e) {
            return Resp::error(['Token is invalid']);
        }

        // Attach user to request
        $request->auth = $user;
        if($user->user_type != 1){
            return Resp::error(['Unauthorized user is not a fan']);
        }

        return $next($request);
    }
}
