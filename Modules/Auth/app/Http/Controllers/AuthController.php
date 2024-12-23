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
use Google_Client;
use Modules\Admin\app\Models\EmailTemplates;
use App\Mail\DynamicEmail;
use App\Mail\EmailHelper;
class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware(AuthMiddleware::class)->except(['register','loginWithGmail','registerWithGmail',  'login', 'verifyEmail', 'verificationEmailToken', 'recoverPassword','resetPassword']);
    }


    public function resetOldEmail(Request $request) {
        $validator = Validator::make($request->all(), [
            'old_email' => 'required|string|email',
            'new_email' => 'required|string|email|max:255|unique:users,email',
            'confirm_email' => 'required|same:new_email',
        ]);
    
        if ($validator->fails()) {
            return Resp::fieldErrors(['field_errors' => $validator->errors()]);
        }  
    
        $user = auth()->user();  
        
        if ($request->old_email !== $user->email) {
            return Resp::error(['message' => 'Old email is incorrect']);
        }
    
        // Generate verification token
        $verification_token = Str::random(30);
        
        // Update user with new email and verification status
        $user->email = $request->new_email;
        $user->email_verified = false;
        $user->verification_token = $verification_token;
        $user->save();
        
        // Send verification email
        $email = new Mailer();
        $email->to($request->new_email);
        $email->subject('Verify Your New Email');
        $email->setBodyByTemplate('verify-email', [
            'verification_token' => $verification_token,
            'user' => $user
        ]);
        $email->send();

        $template = EmailTemplates::where('type','ts_reset_email_confirmations')->first();
        if(!$template){
            return Resp::error(['message' => 'Email template not found']);
        }

        $templateSubject = $template->subject;
        $templateBody = $template->content;
        $recipientEmail = $request->input('new_email'); 
        $dynamicData = [
            '{{name}}' => $user->username,
            '{{email}}' => $user->email,
            '{{link}}' => $user->verification_token,
        ];
        $result = EmailHelper::sendDynamicEmail($dynamicData, $templateSubject, $templateBody, $recipientEmail);

        return Resp::success([
            'message' => 'Email changed successfully. Please verify your new email address.'
        ]);
    }
public function changePassword(Request $request) {
    $validator = Validator::make($request->all(), [
        'old_password' => 'required|string',
        'new_password' => 'required|string|min:8',
        'confirm_password' => 'required|same:new_password',
    ]);

    if ($validator->fails()) {
        return Resp::fieldErrors(['field_errors' => $validator->errors()]);
    }

    $user = auth()->user();
    
    // Check if old password matches
    if (!Hash::check($request->old_password, $user->password)) {
        return Resp::error(['message' => 'Old password is incorrect']);
    }

    $user->password = Hash::make($request->new_password);
    $user->save();
    
    return Resp::success(['message' => 'Password changed successfully']);
}

    public function resetEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:users,email',
        ]);

        if ($validator->fails()) {
            return Resp::fieldErrors(['field_errors' => $validator->errors()]);
        }
        $user = auth()->user();
        $user->email = $request->email;
        $user->save();
        return Resp::success(['message' => 'Email reset successfully']);
    }

    public function verificationEmailToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string'
        ]);
        if ($validator->fails()) {
            return Resp::fieldErrors(['field_errors' => $validator->errors()]);
        }
        $token = $request->token;
        
        $user = AuthUser::where('verification_token', $token)->first();
        if (!$user) {
            return Resp::error(['message' => 'Email verification failed.']);
        }
        $user->email_verified = true;
        $user->save();
        return Resp::success(["current user" => $user], "email verified successfully");
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
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|same:password',
        ]);
        if ($validator->fails()) {
            return Resp::fieldErrors(['field_errors' => $validator->errors()]);
        }
        $user = AuthUser::where('recovery_token', $request->token)->first();
        if(!$user){
            return Resp::error(['error' => 'No user found']);
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
        return Resp::error(['error' => 'No user found']);
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
    public function registerWithGmail(Request $request)

    {
        $validator = Validator::make($request->all(), [
            'google_sso_token' => 'required|string',
            'user_type' => 'required|integer|in:1,2,3',

        ]);

        if ($validator->fails()) {
            return Resp::fieldErrors(['field_errors' => $validator->errors()]);
        }
// print_r($request->all());
        $verification_token = Str::random(30);

        $client = new Google_Client(['client_id' => '554367286106-3knj3b3orb78hh4gj5npg3heiikldtg7.apps.googleusercontent.com']);
        $payload = $client->verifyIdToken($request->google_sso_token);

        if(isset($payload['email'])){
            $email = $payload['email'];

            $exists = AuthUser::where('email',$email)->orWhere('username', $email)->first();

            if($exists){
                return Resp::error(['error' => $email." already exists"]);
            }
            $user = AuthUser::create([
                'username' => $email,
                'email' => $email,
                'user_type' => $request->user_type,
                'password' => 'defaultPassword',
                'email_verified' => true,
                'signin_mode' => 'google_sso'
            ]);
   
            $user_id = $user->id;
            $escort = Profile::create([
                'name' => $user->username,
                'escort_id' => $user->id,
    
            ]);
            return Resp::success(['message' => 'User registered successfully', 'response' => $user], 201);
            
        } else{
            return Resp::error(['error' => 'Unable to register with gmail']);
        }

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
                return Resp::error(['error' => 'Unauthorized']);
            }
        } catch (JWTException $e) {
            return Resp::error(['error' => 'Could not create token']);
        }
        

        $user = JWTAuth::user()->load('profile');
        $email_verified=$user->email_verified;
        if(!$email_verified){
            return Resp::error(['error' => 'Email not verified']);
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

                return Resp::error(['error ' => 'Unauthorized']);
            }
        } catch (JWTException $e) {

            return Resp::error(['error' => 'Could not create token']);
        }
        JWTAuth::setToken($token);

        return Resp::success(['token' => $token]);
    }

    
    public function loginWithGmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'google_sso_token' => 'required|string'
        ]);

        if ($validator->fails()) {
            return Resp::fieldErrors(['field_errors' => $validator->errors()]);
        }

        $client = new Google_Client(['client_id' => env('GOOGLE_SSO_CLIENT_ID')]);
        $payload = $client->verifyIdToken($request->google_sso_token);
        if(isset($payload['email'])){
            $email = $payload['email'];

            try {

                $user = AuthUser::where('email', $email)->with('profile')->first();

                if (!$user) {
                    return Resp::error(['error' => 'User not found']);
                }

                $token = JWTAuth::fromUser($user);
                if (!$token) {
                    return Resp::error(['error' => 'Unauthorized']);
                }

                return Resp::success([
                    'token' => $token,
                    'user' => $user
                ]);
        
            } catch (JWTException $e) {
                return Resp::error(['error' => 'Could not create token']);
            }


        } else {
            return Resp::error(['error' => 'Unauthorized']);
        }
        
    }

    public function logout(Request $request)
    {
        $token = $request->bearerToken() ?: $request->input('token');


        if (!$token) {
            return Resp::error(['error' => 'No token found!!!']);
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
        $user = AuthUser::where('verification_token', $token)->first();
        if (!$user) {
            return view('emailTemplates.token-invalid');
        }
        $user->email_verified = true;
        $user->save();
        return view('emailTemplates.email-verify-succesfully');
    }
   

}