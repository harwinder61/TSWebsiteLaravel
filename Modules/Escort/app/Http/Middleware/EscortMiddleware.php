<?php

namespace Modules\Escort\app\Http\Middleware;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;
use Closure;
use Illuminate\Http\Request;

class EscortMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next){

        try {
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['error' => 'User not found'], 404);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token is invalid'], 401);
        }

        // Attach user to request
        $request->auth = $user;


        //$token=JWTAuth::getToken();

        
        //if($request->email=="test@gmail.com"){
        //    return Response::json(['msg'=>"testing middleware"]);
        //}
    
        return $next($request);
    }
}
