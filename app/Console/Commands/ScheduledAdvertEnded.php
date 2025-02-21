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

class ScheduledAdvertEnded extends Command
{
    // Command signature that will be called by Artisan
    protected $signature = 'app:scheduled-advert-ended';

    // Command description (optional)
    protected $description = 'Send scheduled advert ended emails';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $now = Carbon::now()->format('Y-m-d');
        
        // Get adverts that have ended with all necessary relationships
        $endedAdverts = Subscription::where('end_date', '<=', $now)
            ->with(['user', 'user.profile'])
            ->where('status', 'ACTIVE')
            ->get();

        Log::info('Subscriptions ended on or before ' . $now . ' - Total: ' . $endedAdverts->count());

        foreach ($endedAdverts as $advert) {
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
                        '[ADVERT_LINK]' => env('APP_URL').'/advert/' . $advert->id,
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
        return str_replace(array_keys($data), array_values($data), $template);
    }

    protected function sendSms48hours($phone, $message, $advert)
    {
        // Validate phone number format
        if (!preg_match('/^\+\d{1,15}$/', $phone)) {
            Log::error('Invalid phone number format for Subscription ID: ' . $advert->id . ' - Phone: ' . $phone);
            SmsLogs::create(['message' => 'Invalid phone number format', 'to' => $phone, 'status' => 0, 'user_id' => $advert->user->id, 'from' => env('TWILIO_PHONE_NUMBER')]);
            throw new \Exception("Invalid phone number format: " . $phone);
        }

        Log::info('Sending SMS to: ' . $phone . ' from: ' . env('TWILIO_PHONE_NUMBER'));

        try {
            $client = new Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));
            // $client->messages->create($phone, ['from' => env('TWILIO_PHONE_NUMBER'), 'body' => $message]);

            SmsLogs::create(['message' => $message, 'to' => $phone, 'status' => 1, 'user_id' => $advert->user->id, 'from' => env('TWILIO_PHONE_NUMBER')]);
            return "SMS sent successfully!";
        } catch (\Exception $e) {
            SmsLogs::create(['message' => $message, 'to' => $phone, 'status' => 0, 'user_id' => $advert->user->id, 'from' => env('TWILIO_PHONE_NUMBER')]);
            throw new \Exception("Failed to send SMS: " . $e->getMessage());
        }
    }
}





