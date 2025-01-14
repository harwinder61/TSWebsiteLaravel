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
use Carbon\Carbon;
use App\Console\Commands\ScheduledEmails;
use App\Models\User;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware(AuthMiddleware::class)->except(['register', 'loginWithGmail', 'registerWithGmail',  'login', 'verifyEmail', 'verificationEmailToken', 'recoverPassword', 'resetPassword']);
    }

    // public function login(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'username' => 'required|string',
    //         'password' => 'required|string',
    //     ]);

    //     // Validate the request
    //     if ($validator->fails()) {
    //         return Resp::fieldErrors(['field_errors' => $validator->errors()]);
    //     }

    //     // Extract credentials
    //     $credentials = $request->only('username', 'password');
    //     $loginType = filter_var($request->input('username'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
    //     $credentials = [
    //         $loginType => $request->input('username'),
    //         'password' => $request->input('password'),
    //     ];

    //     // Attempt login
    //     try {
    //         if (!$token = JWTAuth::attempt($credentials)) {
    //             return Resp::error(['error' => 'Unauthorized']);
    //         }
    //     } catch (JWTException $e) {
    //         return Resp::error(['error' => 'Could not create token']);
    //     }

    //     // Set the JWT token
    //     JWTAuth::setToken($token);

    //     // Retrieve the authenticated user
    //     $user = JWTAuth::user()->load('profile');

    //     // Check if email is verified
    //     if (!$user->email_verified) {
    //         return Resp::error(['error' => 'Email not verified']);
    //     }

    //     // Prepare dynamic email data
    //     $dynamicData = [
    //         '[USER_LOGIN]' => $user->username,
    //         '[CUSTOMER_NAME]' => $user->username,
    //         '[CUSTOMER_EMAIL]' => $user->email,
    //     ];

    //     // Send dynamic email (ensure the template exists in the DB)
    //     EmailHelper::sendDynamicEmail(
    //         'Verify_your_new_email_address',
    //         $dynamicData,
    //         $user->email
    //     );


    //     $scheduledEmails = new ScheduledEmails();
    //     // Update last_active_at when the user logs in
    //     // $scheduledEmails->updateLastActiveAt($user->id);

    //     // Call inactivity email logic for the user if applicable
    //     $scheduledEmails->sendInactivityEmails();

    //     // Return response with token and user info
    //     return Resp::success([
    //         'token' => $token,
    //         'user' => $user
    //     ]);
    // }

    // Login Method
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Validate the request
        if ($validator->fails()) {
            return Resp::fieldErrors(['field_errors' => $validator->errors()]);
        }

        // Extract credentials
        $credentials = $request->only('username', 'password');
        $loginType = filter_var($request->input('username'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials = [
            $loginType => $request->input('username'),
            'password' => $request->input('password'),
        ];

        // Attempt login
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return Resp::error(['error' => 'Unauthorized']);
            }
        } catch (JWTException $e) {
            return Resp::error(['error' => 'Could not create token']);
        }

        // Set the JWT token
        JWTAuth::setToken($token);

        // Retrieve the authenticated user
        $user = JWTAuth::user()->load('profile');

        // Check if email is verified
        if (!$user->email_verified) {
            return Resp::error(['error' => 'Email not verified']);
        }


        // Prepare dynamic email data
        $dynamicData = [
            '[USER_LOGIN]' => $user->username,
            '[CUSTOMER_NAME]' => $user->username,
            '[CUSTOMER_EMAIL]' => $user->email,
            '[VERIFY_EMAIL_URL]' => env('WEBAPP_URL') . "/account-verification?token=" . $user->verification_token,
        ];

        // Send dynamic email (ensure the template exists in the DB)
        try {
            EmailHelper::sendDynamicEmail(
                'ts_verify_your_new_email_address',
                $dynamicData,
                $user->email
            );
            Log::info('Verification email sent to: ' . $user->email);
        } catch (\Exception $e) {
            Log::error('Failed to send verification email to ' . $user->email . ': ' . $e->getMessage());
        }
        Log::info('Calling sendInactivityEmails for all inactive users');
        $scheduledEmails = new ScheduledEmails();
        $scheduledEmails->sendInactivityEmails();

        return Resp::success([
            'message' => 'Email sent successfully',
            'token' => $token,
            'user' => $user
        ]);
    }

    // public function resetOldEmail(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'old_email' => 'required|string|email',
    //         'new_email' => 'required|string|email|max:255|unique:users,email',
    //         'confirm_email' => 'required|same:new_email',
    //     ]);
    
    //     if ($validator->fails()) {
    //         return Resp::fieldErrors(['field_errors' => $validator->errors()]);
    //     }
    
    //     $user = auth()->user();
    
    //     if ($request->old_email !== $user->email) {
    //         return Resp::error(['message' => 'Old email is incorrect']);
    //     }
    
    //     // Generate verification token
    //     $verification_token = Str::random(30);
    
    //     // Update user with new email and verification status
    //     $user->email = $request->new_email;
    //     $user->email_verified = false;
    //     $user->verification_token = $verification_token;
    //     $user->save();
    
    //     EmailHelper::sendDynamicEmail(
    //         'Email_Change_Request',
    //         ['[USER_LOGIN]' => $user->username, '[USER_EMAIL]' => $request->old_email,],
    //         $request->old_email
    //     );
    //     return Resp::success([
    //         'message' => 'Email changed successfully. Please verify your new email address.'
    //     ]);
    // }
    public function resetOldEmail(Request $request)
{
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

    // Send email to old email address
    EmailHelper::sendDynamicEmail(
        'Email_Change_Request',
        ['[USER_LOGIN]' => $user->username, '[USER_EMAIL]' => $request->old_email,],
        $request->old_email
    );

    // Send verification email to new email address
    EmailHelper::sendDynamicEmail(
        'ts_verify_your_new_email_address',
        ['[USER_LOGIN]' => $user->username, '[USER_EMAIL]' => $user->email,'[VERIFY_EMAIL_URL]' => env('WEBAPP_URL') . "/account-verification?token=" . $user->verification_token, '' => $verification_token],
        $user->email
    );

    return Resp::success([
        'message' => 'Email changed successfully. Please verify your new email address.'
    ]);
}


    public function changePassword(Request $request)
    {
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
        EmailHelper::sendDynamicEmail(
            'ts_new_password_notification',
            ['[USER_LOGIN]' => $user->username, '[USER_EMAIL]' => $user->email],
            $user->email
        );
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
        $template = EmailTemplates::where('type', 'ts_verify_your_new_email_address')->first();
        if (!$template) {
            return Resp::error(['message' => 'Email template not found']);
        }
        $templateSubject = $template->subject;
        $templateBody = $template->content;
        $recipientEmail = $user->email; // You can pass this via API request
        $dynamicData = [
            '[USER_LOGIN]' => $user->username,
            '[USER_EMAIL]' => $user->email,
        ];
        $result = EmailHelper::sendDynamicEmail($dynamicData, $templateSubject, $templateBody, $recipientEmail);
        return Resp::success(["current user" => $user], "email verified successfully");
    }


    public function verificationToken(Request $request)
    {
        $user = AuthUser::where('email', auth()->user()->email)->first();
        if (!$user) {
            return Resp::error(['message' => 'User not found'],);
        }
        return Resp::success([
            'verification_token' => $user->verification_token
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|same:password',
        ]);
        if ($validator->fails()) {
            return Resp::fieldErrors(['field_errors' => $validator->errors()]);
        }
        $user = AuthUser::where('recovery_token', $request->token)->first();
        if (!$user) {
            return Resp::error(['error' => 'No user found']);
        }
        $user->password = Hash::make($request->password);
        $user->recovery_token = null;
        $user->save();
        EmailHelper::sendDynamicEmail(
            'ts_new_password_notification',
            ['[USER_LOGIN]' => $user->username, '[USER_EMAIL]' => $user->email],
            $user->email
        );
        return Resp::success(['message' => 'Password reset successfully']);
    }

    public function recoverPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
        ]);
        if ($validator->fails()) {
            return Resp::fieldErrors(['field_errors' => $validator->errors()]);
        }
        $token = Str::random(30);
        $user = AuthUser::where('email', $request->email)->first();
        if (!$user) {
            return Resp::error(['error' => 'No user found']);
        }
        $user->recovery_token = $token;
        $user->save();
        $email = new Mailer();
        $email->to($user->email);
        $email->subject('Password Recovery');
        $email->setBodyRecoveryEmail('recover-password', ['recovery_token' => $token, 'user' => $user]);
        $email->send();
        return Resp::success(['message' => 'Password recovery token sent successfully']);
    }


    public function register(Request $request)
    {
        // Validate the incoming request data
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

        // If validation fails, return field errors
        if ($validator->fails()) {
            return Resp::fieldErrors(['field_errors' => $validator->errors()]);
        }

        // Generate a verification token
        $verification_token = Str::random(30);

        // Create the user record
        $user = AuthUser::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => $request->user_type,
            'verification_token' => $verification_token,
            'email_verified' => false,
            'last_active_at' => Carbon::now(),
        ]);

        // Send verification email
        //    $email = new Mailer();
        //    $email->to($user->email);
        //    $email->subject('Test Email');
        //    $email->setBodyByTemplate('verify-email', ['verification_token' => $verification_token, 'user' => $user]);
        //    $email->send();

        // Create user profile
        $escort = Profile::create([
            'name' => $user->username,
            'escort_id' => $user->id,
        ]);

        // One-liner call to send dynamic email
        EmailHelper::sendDynamicEmail(
            'ts_email_verification',
            [
                '[USER_LOGIN]' => $user->username,
                '[USER_EMAIL]' => $user->email,
                '[VERIFIED_EMAIL_LINK]' => env('WEBAPP_URL') . "/account-verification?token=" . $verification_token
            ],
            $user->email
        );

        // Return success response
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

        if (isset($payload['email'])) {
            $email = $payload['email'];

            $exists = AuthUser::where('email', $email)->orWhere('username', $email)->first();

            if ($exists) {
                return Resp::error(['error' => $email . " already exists"]);
            }
            $user = AuthUser::create([
                'username' => $email,
                'email' => $email,
                'user_type' => $request->user_type,
                'password' => 'defaultPassword',
                'email_verified' => false,
                'signin_mode' => 'google_sso',
                'verification_token' => $verification_token,
            ]);

            $user_id = $user->id;
            $escort = Profile::create([
                'name' => $user->username,
                'escort_id' => $user->id,

            ]);

            // One-liner call to send dynamic email
            EmailHelper::sendDynamicEmail(
                'ts_email_verification',
                [
                    '[USER_LOGIN]' => $user->username,
                    '[USER_EMAIL]' => $user->email,
                    '[VERIFIED_EMAIL_LINK]' => env('WEBAPP_URL') . "/account-verification?token=" . $verification_token,
                ],
                $user->email
            );
            return Resp::success(['message' => 'User registered successfully', 'response' => $user], 201);
        } else {
            return Resp::error(['error' => 'Unable to register with gmail']);
        }
    }


    // public function login(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'username' => 'required|string',
    //         'password' => 'required|string',
    //     ]);


    //     if ($validator->fails()) {
    //         return Resp::fieldErrors(['field_errors' => $validator->errors()]);
    //     }

    //     $credentials = $request->only('username', 'password');
    //     $loginType = filter_var($request->input('username'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
    //     $credentials = [
    //         $loginType => $request->input('username'),
    //         'password' => $request->input('password'),
    //     ];

    //     try {
    //         if (!$token = JWTAuth::attempt($credentials)) {
    //             return Resp::error(['error' => 'Unauthorized']);
    //         }
    //     } catch (JWTException $e) {
    //         return Resp::error(['error' => 'Could not create token']);
    //     }


    //     $user = JWTAuth::user()->load('profile');
    //     $email_verified=$user->email_verified;
    //     if(!$email_verified){
    //         return Resp::error(['error' => 'Email not verified']);
    //     }
    //     $profile = [
    //         'username' => $user->username,
    //         'email' => $user->email,
    //         'user_type' => $user->user_type,

    //     ];

    //     return Resp::success([
    //         'token' => $token,
    //         'user' => $user
    //     ]);

    //     $credentials = $request->only('username', 'password');
    //     // Determine if the input is an email or username
    //     $loginType = filter_var($request->input('username'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
    //     $credentials = [
    //         $loginType => $request->input('username'),
    //         'password' => $request->input('password'),
    //     ];

    //     try {
    //         if (!$token = JWTAuth::attempt($credentials)) {

    //             return Resp::error(['error ' => 'Unauthorized']);
    //         }
    //     } catch (JWTException $e) {

    //         return Resp::error(['error' => 'Could not create token']);
    //     }
    //     JWTAuth::setToken($token);
    //     EmailHelper::sendDynamicEmail('Verify_your_new_email_address', 
    //     ['[CUSTOMER_NAME]' => $user->username, '[CUSTOMER_EMAIL]' => $user->email], 
    //     $user->email);
    //     return Resp::success(['token' => $token]);
    // }
    // //// akkkkkkkk////////



    //     public function login(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'username' => 'required|string',
    //         'password' => 'required|string',
    //     ]);

    //     // Validate the request
    //     if ($validator->fails()) {
    //         return Resp::fieldErrors(['field_errors' => $validator->errors()]);
    //     }

    //     // Extract credentials
    //     $credentials = $request->only('username', 'password');
    //     $loginType = filter_var($request->input('username'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
    //     $credentials = [
    //         $loginType => $request->input('username'),
    //         'password' => $request->input('password'),
    //     ];

    //     // Attempt login
    //     try {
    //         if (!$token = JWTAuth::attempt($credentials)) {
    //             return Resp::error(['error' => 'Unauthorized']);
    //         }
    //     } catch (JWTException $e) {
    //         return Resp::error(['error' => 'Could not create token']);
    //     }

    //     // Set the JWT token
    //     JWTAuth::setToken($token);

    //     // Retrieve the authenticated user
    //     $user = JWTAuth::user()->load('profile');

    //     // Check if email is verified
    //     if (!$user->email_verified) {
    //         return Resp::error(['error' => 'Email not verified']);
    //     }

    //     // Check if user has been inactive for 28 days or more and hasn't received the email
    //     if ($user->last_active_at && Carbon::parse($user->last_active_at)->lt(Carbon::now()->subDays(28)) && $user->drop_mail == 0) {
    //         // Prepare dynamic email data
    //         $dynamicData = [
    //             '[USER_LOGIN]' => $user->username,  // This will replace [USER_LOGIN] with the username
    //             '[CUSTOMER_NAME]' => $user->username,
    //             '[CUSTOMER_EMAIL]' => $user->email,
    //         ];

    //         // Send dynamic email (ensure the template exists in the DB)
    //         EmailHelper::sendDynamicEmail(
    //             'Verify_your_new_email_address',  // Template type
    //             $dynamicData,  // Dynamic data
    //             $user->email  // Recipient email
    //         );

    //         // Update drop_mail to 1 to ensure the email is only sent once
    //         $user->drop_mail = 1;
    //         $user->save();
    //     }

    //     // Update last_active_at to the current time when the user logs in
    //     $user->last_active_at = Carbon::now();
    //     $user->save();

    //     // Return response with token and user info
    //     return Resp::success([
    //         'token' => $token,
    //         'user' => $user
    //     ]);
    // }



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
        if (isset($payload['email'])) {
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

    public function verifyEmail($token, Request $request)
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
