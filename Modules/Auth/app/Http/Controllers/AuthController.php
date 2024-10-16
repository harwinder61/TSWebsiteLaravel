<?php

namespace Modules\Auth\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Modules\Auth\Entities\User;
use Modules\Escort\app\Models\Escort;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Modules\Auth\app\Http\Middleware\AuthMiddleware;
use Modules\Escort\app\Models\ProfileRates;
use Modules\Escort\app\Models\Profile;
use App\Services\Resp;

class AuthController extends Controller
{

    public function __construct(){
        $this->middleware(AuthMiddleware::class)->except(['register','login']);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'user_type'=>'required|integer|in:1,2',
            'password_confirmation'=>'required|same:password',
        ],[
            'user_type.in'=>'The user type must be either 1 or 2',
            'password.confirmed'=>'The password and confirm password do not match',
        ]);

        if ($validator->fails()) {
            return Resp::error(['error' => $validator->errors()], 422);
        }

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => $request->user_type,
        ]);

        $user_id=$user->id;
        
        // Log::info("user_id: ".$user_id);
        $escort=Profile::create([
           'name'=>$user->username,
           'escort_id'=>$user->id,

        ]);

        echo $escort->toSql();
        
        return Resp::success(['message' => 'User registered successfully', 'response' => $user], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        

        if ($validator->fails()) {
            return Resp::error(['error' => $validator->errors()], 422);
            
        }

        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) 
            {

                    return Resp::error(['error ' => 'Unauthorized'], 401);
            }
        } catch (JWTException $e) {

            return Resp::error(['error' => 'Could not create token'], 401);
        }
        JWTAuth::setToken($token);

        return Resp::success(['token' => $token]);
    }

    public function logout(Request $request)
    {
        $token = $request->bearerToken() ?: $request->input('token');


        if(!$token){
            return Resp::error(['error'=>'No token found!!!'],401);
        }
        try{

            JWTAuth::setToken($token);
            JWTAuth::invalidate($token);
            return Resp::success(["message"=>"Successfully Logged out"],201);

        }
        catch(JWTException $e){

                return Resp::error(['error' => 'Could not log out, token might be invalid'], 401);
    
        }

        return Resp::success(['message' => 'Successfully logged out']);
    }

    public function me(Request $request)
    {

        $token=JWTAuth::getToken();
        if (!$token) {
        return Resp::error(['error' => 'No token provided'], 401);
        }
        try{


        JWTAuth::setToken($token);
        $user=JWTAuth::parseToken()->authenticate();
        if(!$user){
            $user="No User found!";
         
        }

        return Resp::success(["current user"=>$user],201);
        }catch(JWTException $e){


            // Return a response based on the type of JWTException
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return Resp::error(['error' => 'Token has expired'], 401);
            } elseif ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return Resp::error(['error' => 'Token is invalid'], 401);
                print_r($e);
           
            } elseif ($e instanceof \Tymon\JWTAuth\Exceptions\JWTException) {
                return Resp::error(['error' => 'Token is malformed or could not be decoded'], 401);
            } else {
                return Resp::error(['error' => 'An unexpected error occurred'], 500);
            }

        }
    }
}
