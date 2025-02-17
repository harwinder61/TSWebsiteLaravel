<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Escort\app\Models\Reviews;
use Carbon\Carbon;
use App\Models\Plan;
use App\Mail\SmsHelper;
use Twilio\Rest\Client;
use Modules\Admin\app\Models\SmsLogs;
use Modules\Auth\app\Models\AuthUser;
use Modules\Escort\app\Models\Orders;
use Modules\Escort\app\Models\Subscription;

class ScheduledSms extends Command
{
    // Command signature that will be called by Artisan
    protected $signature = 'app:scheduled-sms';
    

    // Command description (optional)
    protected $description = 'Send scheduled sms';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->handleFortyEightHours();
        return true;
    }
    
    protected function handleFortyEightHours()
    {
        $now = Carbon::now();
        $twoDaysLater = $now->copy()->addHours(48);
        $targetDate = $twoDaysLater->format('Y-m-d');
        
        // Get adverts ending in 48 hours with all necessary relationships
        $expiringAdverts = Subscription::where('end_date', '=', $targetDate)
            ->with(['user', 'user.profile'])  // Added user and profile relationships
            ->where('status', 'ACTIVE')
            ->get();

        Log::info('Subscriptions expiring on ' . $targetDate . ' - Total: ' . $expiringAdverts->count());
        
        foreach ($expiringAdverts as $advert) {
            Log::info('Subscription ID: ' . $advert->id . 
                     ' | User Email: ' . ($advert->user ? $advert->user->email : 'No Email') . 
                     ' | Expiry Date: ' . $advert->end_date);
            
            if ($advert->user && $advert->user->profile && $advert->user->profile->phone_number) {
                try {
                    $phone = $advert->user->profile->phone_number;

                    // Fetch the SMS template
                    $templateData = SmsHelper::getSmsTemplateByType('advert_ending_in_48_hours');
                    $template = $templateData->content ?? '';

                    // Prepare dynamic data
                    $dynamicData = [
                        '[USER_LOGIN]' => $advert->user->username ?? $advert->user->name,
                        '[ADVERT_TITLE]' => $advert->plan->title ?? 'Subscription',
                        '[ADVERT_LINK]' => url('/dashboard/subscription'),
                    ];

                    // Replace placeholders in the template
                    $message = $this->replacePlaceholders($template, $dynamicData);
                    $message = strip_tags($message); // Remove HTML tags

                    // Call the method to send SMS and save logs
                    $smsResponse = $this->sendSms48hours($phone, $message, $advert);
                    Log::info('SMS Response: ' . $smsResponse);
                } catch (\Exception $e) {
                    Log::error('Failed to send SMS for Subscription ID: ' . $advert->id . ' - Error: ' . $e->getMessage());
                }
            } else {
                Log::info('Skipping SMS for Subscription ID: ' . $advert->id . ' - No valid user profile or phone number');
            }
        }
    }

    protected function replacePlaceholders($template, $data)
    {
        foreach ($data as $key => $value) {
            $template = str_replace($key, $value, $template);
        }
        return $template;
    }

    protected function sendSms48hours($phone, $message, $advert)
    {
        // Validate phone number format
        if (!preg_match('/^\+\d{1,15}$/', $phone)) {
            Log::error('Invalid phone number format for Subscription ID: ' . $advert->id . ' - Phone: ' . $phone);
            SmsLogs::create([
                'message' => 'Invalid phone number format',
                'to' => $phone,
                'status' => 0, // Failed
                'user_id' => $advert->user->id,
                'from' => env('TWILIO_PHONE_NUMBER'),
            ]);
            throw new \Exception("Invalid phone number format: " . $phone);
        }
    
        Log::info('Sending SMS to: ' . $phone . ' from: ' . env('TWILIO_PHONE_NUMBER'));
    
        try {
            $sid = env('TWILIO_SID');
            $token = env('TWILIO_TOKEN');
            $twilioNumber = env('TWILIO_PHONE_NUMBER');
    
            // Send SMS via Twilio
            $client = new Client($sid, $token);
            $client->messages->create(
                $phone,
                [
                    'from' => $twilioNumber,
                    'body' => $message
                ]
            );
    
            // Log successful SMS
            SmsLogs::create([
                'message' => $message,
                'to' => $phone,
                'status' => 1, // Success
                'user_id' => $advert->user->id,
                'from' => $twilioNumber,
            ]);
    
            return "SMS sent successfully!";
        } catch (\Exception $e) {
            // Log failed SMS
            SmsLogs::create([
                'message' => $message,
                'to' => $phone,
                'status' => 0, // Failed
                'user_id' => $advert->user->id,
                'from' => env('TWILIO_PHONE_NUMBER'),
            ]);
    
            throw new \Exception("Failed to send SMS: " . $e->getMessage());
        }
    }

    protected function AdvertEnded()
    {
        $now = Carbon::now()->format('Y-m-d');
        
        // Get adverts that have ended with all necessary relationships
        $endedAdverts = Subscription::where('end_date', '<=', $now)
            ->with(['user', 'user.profile'])
            ->where('status', 'ACTIVE')
            ->get();
    
        Log::info('Subscriptions ended on or before ' . $now . ' - Total: ' . $endedAdverts->count());
    
        foreach ($endedAdverts as $advert) {
            print_r($advert);
            Log::info('Subscription ID: ' . $advert->id . 
                     ' | User Email: ' . ($advert->user ? $advert->user->email : 'No Email') . 
                     ' | Expiry Date: ' . $advert->end_date);
            
            if ($advert->user && $advert->user->profile && $advert->user->profile->phone_number) {
                try {
                    $phone = $advert->user->profile->phone_number;
    
                    // Fetch the SMS template for advert ended
                    $templateData = SmsHelper::getSmsTemplateByType('advert_ended_renew');
                    $template = $templateData->content ?? '';
    
                    // Prepare dynamic data
                    $dynamicData = [
                        '[USER_LOGIN]' => $advert->user->username ?? $advert->user->name,
                        '[ADVERT_TITLE]' => $advert->plan->title ?? 'Subscription',
                        '[ADVERT_LINK]' => url('/dashboard/subscription'),
                    ];
    
                    // Replace placeholders in the template
                    $message = $this->replacePlaceholders($template, $dynamicData);
                    $message = strip_tags($message); // Remove HTML tags
    
                    // Call the method to send SMS and save logs
                    $smsResponse = $this->sendSms48hours($phone, $message, $advert);
                    Log::info('SMS Response: ' . $smsResponse);
    
                    // Log successful SMS
                    SmsLogs::create([
                        'message' => $message,
                        'to' => $phone,
                        'status' => 1, // Success
                        'user_id' => $advert->user->id,
                        'from' => env('TWILIO_PHONE_NUMBER'),
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send SMS for Subscription ID: ' . $advert->id . ' - Error: ' . $e->getMessage());
    
                    // Log failed SMS
                    SmsLogs::create([
                        'message' => $message,
                        'to' => $phone,
                        'status' => 0, // Failed
                        'user_id' => $advert->user->id,
                        'from' => env('TWILIO_PHONE_NUMBER'),
                    ]);
                }
            } else {
                Log::info('Skipping SMS for Subscription ID: ' . $advert->id . ' - No valid user profile or phone number');
            }
        }
    }



}

