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

class ScheduledInactive extends Command
{
    // Command signature that will be called by Artisan
    protected $signature = 'app:scheduled-inactive-users';
    
    // Command description (optional)
    protected $description = 'Send scheduled SMS';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->inactiveUsers();
    }

    public function inactiveUsers()
    {
        $this->info('Cron job started - Checking inactive users...');
        Log::info('Cron job started - Checking inactive users...');
        
        $dateThreshold = Carbon::now()->subDays(14); // 14 days ago
        
        // Get users who have been inactive for 14 days
        $inactiveUsers = AuthUser::where('last_active_at', '<=', $dateThreshold)
            ->with(['profile']) // Assuming there's a profile relationship
            ->get();
        
        Log::info('Inactive users for 14 days - Total: ' . $inactiveUsers->count());
        
        foreach ($inactiveUsers as $user) {
            $this->info('User ID: ' . $user->id . ' | User Email: ' . $user->email);
    
            if ($user->profile && $user->profile->phone_number) {
                // Check if the SMS has already been sent with status true
                $smsLog = SmsLogs::where('user_id', $user->id)
                    ->where('status', true) // Check for status true instead of message_sent
                    ->first();
    
                if ($smsLog) {
                    Log::info('Skipping SMS for User ID: ' . $user->id . ' - Message already sent');
                    continue; // Skip sending SMS if already sent
                }
    
                try {
                    $phone = $user->profile->phone_number;
    
                    // Fetch the SMS template for inactive users
                    $templateData = SmsHelper::getSmsTemplateByType('come_back_to_transbunnies_after_14_days');
                    $template = $templateData->content ?? '';
    
                    // Prepare dynamic data
                    $dynamicData = [
                        '[USER_LOGIN]' => $user->username ?? $user->name,
                        '[ADVERT_TITLE]' => $user->subscription->plan->title ?? 'Plan',
                        '[ADVERT_LINK]' => url('/login'), // Adjust the link as necessary
                    ];
    
                    // Replace placeholders in the template
                    $message = $this->replacePlaceholders($template, $dynamicData);
                    $message = strip_tags($message); // Remove HTML tags
    
                    // Call the method to send SMS and save logs
                    $smsResponse = $this->sendSms($phone, $message, $user);
                    Log::info('SMS Response: ' . $smsResponse);
    
                    // After sending the SMS, log it with message_sent as true
                    SmsLogs::create([
                        'message' => $message, 
                        'to' => $phone, 
                        'status' => 1, 
                        'user_id' => $user->id, 
                        'from' => env('TWILIO_PHONE_NUMBER'), 
                        'message_sent' => true
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send SMS for User ID: ' . $user->id . ' - Error: ' . $e->getMessage());
                    SmsLogs::create([
                        'message' => $message, 
                        'to' => $phone, 
                        'status' => 0, 
                        'user_id' => $user->id, 
                        'from' => env('TWILIO_PHONE_NUMBER'), 
                        'message_sent' => false
                    ]);
                }
            } else {
                Log::info('Skipping SMS for User ID: ' . $user->id . ' - No valid phone number');
            }
        }
    
        $this->info('Completed sending SMS to inactive users');
        Log::info('Cron job completed - Finished sending SMS to inactive users');
    }

    protected function replacePlaceholders($template, $data)
    {
        return str_replace(array_keys($data), array_values($data), $template);
    }

    protected function sendSms($phone, $message, $user)
    {
        Log::info('>>>>>>>>><<<<<<<<<<......Cron job executing sendSms function ..............>>>>>>><<<<<<<<');
        // Validate phone number format
        if (!preg_match('/^\+\d{1,15}$/', $phone)) {
            Log::error('Invalid phone number format for User ID: ' . $user->id . ' - Phone: ' . $phone);
            SmsLogs::create([
                'message' => 'Invalid phone number format', 
                'to' => $phone, 
                'status' => 1, 
                'user_id' => $user->id, 
                'message_sent' => true,
                'from' => env('TWILIO_PHONE_NUMBER')
            ]);
            throw new \Exception("Invalid phone number format: " . $phone);
        }

        Log::info('Sending SMS to: ' . $phone . ' from: ' . env('TWILIO_PHONE_NUMBER'));

        try {
            $client = new Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));
            // $client->messages->create($phone, ['from' => env('TWILIO_PHONE_NUMBER'), 'body' => $message]);
            SmsLogs::create([
                'message' => $message, 
                'to' => $phone, 
                'status' => 1, 
                'user_id' => $user->id, 
                'message_sent' => true,
                'from' => env('TWILIO_PHONE_NUMBER')
            ]);
            return "SMS sent successfully!";
        } catch (\Exception $e) {
            SmsLogs::create([
                'message' => $message, 
                'to' => $phone, 
                'status' => 0, 
                'user_id' => $user->id, 
                'message_sent' => false,
                'from' => env('TWILIO_PHONE_NUMBER')
            ]);
            throw new \Exception("Failed to send SMS: " . $e->getMessage());
        }
    }



}
