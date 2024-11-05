<?php

namespace Modules\Auth\app\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Modules\Auth\app\Models\AuthUser;
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
use App\Services\EmailService as Mailer;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware(AuthMiddleware::class)->except(['register', 'login', 'verifyEmail', 'verificationEmailToken']);
    }

    public function verificationEmailToken($token,Request $request){
        if (!$token) {
            return Resp::error(['error' => 'No token provided'], 401);
        }
        // Fix typo in column name from 'verfiication_token' to 'verification_token'
        $user = AuthUser::where('verification_token', $token)->first();
        if (!$user) {
            return Resp::error(['error' => 'Token is invalid'], 401);
        }
        $user->email_verified = true;
        $user->save();
        return Resp::success(["current user" => $user], 201);
    }


    public function verificationToken(Request $request){
        $user = AuthUser::where('email', auth()->user()->email)->first();
        if (!$user) {
            return Resp::error(['message' => 'User not found'],);
        }
        
        return Resp::success([
            'verification_token' => $user->verification_token
        ]);
    }

    public function resetPassword(Request $request){
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|same:password',
        ]);
        if ($validator->fails()) {
            return Resp::fieldErrors(['field_errors' => $validator->errors()]);
        }
        $user = AuthUser::where('email',$request->email)->where('recovery_token',$request->token)->first();
        if(!$user){
            return Resp::error(['error' => 'No user found'], 401);
        }
        $user->password = Hash::make($request->password);
        $user->recovery_token = null;
        $user->save();
        return Resp::success(['message' => 'Password reset successfully']);
        
    }

   public function recoverPassword(Request $request){
    $validator = Validator::make($request->all(), [
        'email' => 'required|string|email|max:255',
    ]);
    if ($validator->fails()) {
        return Resp::fieldErrors(['field_errors' => $validator->errors()]);
    }
    $token = Str::random(30);
    $user = AuthUser::where('email',$request->email)->first();
    if(!$user){
        return Resp::error(['error' => 'No user found'], 401);
    }
    $user->recovery_token = $token;
    $user->save();
    $email = new Mailer();
    $email->to($user->email);
    $email->subject('Password Recovery');
    $email->setBodyRecoveryEmail('recover-password',['recovery_token' => $token,'user' => $user]);
    $email->send();
    return Resp::success(['message' => 'Password recovery token sent successfully']);

   }

    public function register(Request $request)

    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'user_type' => 'required|integer|in:1,2,3',
            'password_confirmation' => 'required|same:password',
        ], [
            'user_type.in' => 'The user type must be either 1 or 2 or 3',
            'password.confirmed' => 'The password and confirm password do not match',
        ]);

        if ($validator->fails()) {
            return Resp::fieldErrors(['field_errors' => $validator->errors()]);
        }

        $verification_token = Str::random(30);

        $user = AuthUser::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => $request->user_type,
            'verification_token' => $verification_token,
            'email_verified' => false,
        ]);
        $email = new Mailer();
        $email->to($user->email);
        $email->subject('Test Email');
        $email->setBodyByTemplate('verify-email',['verification_token' => $verification_token,'user' => $user]);
        $email->send();

        $user_id = $user->id;
        $escort = Profile::create([
            'name' => $user->username,
            'escort_id' => $user->id,

        ]);



        return Resp::success(['message' => 'User registered successfully', 'response' => $user], 201);
    }

   
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);


        if ($validator->fails()) {
            return Resp::fieldErrors(['field_errors' => $validator->errors()]);
        }

        $credentials = $request->only('username', 'password');
        $loginType = filter_var($request->input('username'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials = [
            $loginType => $request->input('username'),
            'password' => $request->input('password'),
        ];

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return Resp::error(['error' => 'Unauthorized'], 401);
            }
        } catch (JWTException $e) {
            return Resp::error(['error' => 'Could not create token'], 401);
        }
        

        $user = JWTAuth::user()->load('profile');
        $email_verified=$user->email_verified;
        if(!$email_verified){
            return Resp::error(['error' => 'Email not verified'], 401);
        }
        $profile = [
            'username' => $user->username,
            'email' => $user->email,
            'user_type' => $user->user_type,
            
        ];

        return Resp::success([
            'token' => $token,
            'user' => $user
        ]);

        $credentials = $request->only('username', 'password');
        // Determine if the input is an email or username
        $loginType = filter_var($request->input('username'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials = [
            $loginType => $request->input('username'),
            'password' => $request->input('password'),
        ];

        try {
            if (!$token = JWTAuth::attempt($credentials)) {

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


        if (!$token) {
            return Resp::error(['error' => 'No token found!!!'], 401);
        }
        
        try {

            JWTAuth::setToken($token);
            JWTAuth::invalidate($token);
            return Resp::success(["message" => "Successfully Logged out"], 201);
        } catch (JWTException $e) {

            return Resp::error(['error' => 'Could not log out, token might be invalid'], 401);
        }

        return Resp::success(['message' => 'Successfully logged out']);
    }

    public function me(Request $request)
    {

        $token = JWTAuth::getToken();
        if (!$token) {
            return Resp::error(['error' => 'No token provided'], 401);
        }
        try {

            JWTAuth::setToken($token);
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                $user = "No User found!";
            }
            return Resp::success(["current user" => $user], 201);
        } catch (JWTException $e) {

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

    public function verifyEmail($token,Request $request)
    {

        if (!$token) {
         return Resp::error(['error' => 'No token provided'], 401);
        }
        
        $user = AuthUser::where('verfiication_token', $token)->first();
        if (!$user) {
            //return Resp::error(['Token is invalid']);
            return view('emailTemplates.token-invalid');
        }

        $user->email_verified = true;
        $user->save();
        //return Resp::success(['message' => 'Email verified successfully']);
        return Resp::success(["current user" => $user], 201);
    }

     



}
