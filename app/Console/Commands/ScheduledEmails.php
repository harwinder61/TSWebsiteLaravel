<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\BaseSubscription;
use App\Models\User;
use App\Mail\EmailHelper;
use App\Services\Resp;
use Carbon\Carbon;
use App\Models\BaseProfile;
use App\Models\Plan;

class ScheduledEmails extends Command
{
    // Command signature that will be called by Artisan
    protected $signature = 'app:scheduled-emails';

    // Command description (optional)
    protected $description = 'Send scheduled emails';

    public function __construct()
    {
        parent::__construct();
    }

    // // Command logic// Command logic
    // public function handle()
    // {
    //     $now = Carbon::now();
    //     $nextDay = $now->addDay();   
    //     $nextDate = $nextDay->format('Y-m-d'); 
    //     // Get expired subscriptions
    //     $expiredSubscriptions = BaseSubscription::where('end_date', '=', $nextDate)
    //         ->with('escort')
    //         ->with('profile')
    //         ->with('user')
    //         ->where('status', 'ACTIVE')
    //         ->get();
    //     // If you want to return them in the command output:
    //     $output = $expiredSubscriptions->toArray();
    
    //     // Alternatively, you can use Log to record the expired subscriptions
    //     Log::info('Expired Subscriptions: ', $output);
    
    //     // Loop through expired subscriptions to update their status and send emails
    //     foreach ($expiredSubscriptions as $subscription) {
    //         print_r($subscription);
    //         // Send email to the user
    //         $this->sendExpirationEmail($subscription->escort_id);
    
    //         // Update subscription status to 'expired'
    //         // $subscription->status = 'expired';
    //         $subscription->save();
    //     }
    
    //     // Return the expired subscriptions as an output
    //     return $output;  // Returning the expired subscriptions data
    // }
    

//  public function handle()
//     {
//         $now = Carbon::now();
//         $nextDay = $now->addDay();   
//         $nextDate = $nextDay->format('Y-m-d');

//         // Get expired subscriptions
//         $expiredSubscriptions = BaseSubscription::where('end_date', '=', $nextDate)
//             ->with('escort')
//             ->with('profile')
//             ->with('user')
//             ->where('status', 'ACTIVE')
//             ->get();

            

//             Log::error('Expired Subscription TOTAL : '.$expiredSubscriptions->count());
//        $count =   1;
//        foreach ($expiredSubscriptions as $subscription) {
//         if ($subscription->escort && $subscription->escort->user) {
//             Log::error('Expired Subscription SINGLE: '.$count.' >>> '.$subscription->escort_id .' to email '.$subscription->escort->user->email);
//         } else {
//             Log::error('Expired Subscription SINGLE: '.$count.' >>> '.$subscription->escort_id .' (No user or escort found)');
//         }
//         // Send email to the user
//         try {
//             $this->sendExpirationEmail($subscription);  
//             //code...
//         } catch (\Throwable $th) {
//             Log::error('ERROR  WHILE SENDING EMAIL: '.$count.' >>> '.$subscription->escort_id .' >>> '.$th->getMessage());
//             //throw $th;
//         }
        
//         $subscription->is_24_hours = 1;
//         $subscription->save();
    
//         // Update subscription status to 'expired'
//         // $subscription->status = 'expired';
//         $count++;
//         $subscription->save();
//     }

//             return true; 

  
//     }
//     // New method to send an email to the user with the expired subscription// app/Console/Commands/ScheduledEmails.php

//     private function sendExpirationEmail($subscription)
//     {
//         if ($subscription->escort && $subscription->escort->user) {
//             $email = $subscription->escort->user->email;
//             EmailHelper::sendDynamicEmail('subscription_expired', 
//                 ['[USER_LOGIN]' => $subscription->escort->user->username, '[USER_EMAIL]' => $email], 
//                 $email);
//             return Resp::success(['message' => 'Subscription expired successfully']);
//         } else {
//             // Handle the case when $subscription->escort or $subscription->escort->user is null
//             // For example, you can log an error or return a specific response
//             Log::error('Escort or user is null for subscription ' . $subscription->id);
//             return Resp::error(['message' => 'Failed to send expiration email']);
//         }
//     }

// }


public function handle()
{
    $now = Carbon::now();
    $nextDay = $now->addDay();   
    $nextDate = $nextDay->format('Y-m-d');

    // Get expired subscriptions
    $expiredSubscriptions = BaseSubscription::where('end_date', '=', $nextDate)
        ->with('escort')
        ->with('profile')
        ->with('user')
        ->where('status', 'ACTIVE')
        
        ->get();

    Log::error('Expired Subscription TOTAL : '.$expiredSubscriptions->count());
    $count = 1;
    foreach ($expiredSubscriptions as $subscription) {
        if ($subscription->escort && $subscription->escort->user) {
            Log::error('Expired Subscription SINGLE: '.$count.' >>> '.$subscription->escort_id .' to email '.$subscription->escort->user->email);
        } else {
            Log::error('Expired Subscription SINGLE: '.$count.' >>> '.$subscription->escort_id .' (No user or escort found)');
        }
        // Send email to the user
        try {
            $this->sendExpirationEmail($subscription);  
        } catch (\Throwable $th) {
            Log::error('ERROR  WHILE SENDING EMAIL: '.$count.' >>> '.$subscription->escort_id .' >>> '.$th->getMessage());
        }
        
        $subscription->is_24__hours = 1;
        $subscription->save();
    
        // Update subscription status to 'expired'
        // $subscription->status = 'expired';
        $count++;
        $subscription->save();
    }

    return true; 
}

private function sendExpirationEmail($subscription)
{
    if ($subscription->escort && $subscription->escort->user) {
        $email = $subscription->escort->user->email;
        EmailHelper::sendDynamicEmail('subscription_expired', 
            ['[User Login]' => $subscription->escort->user->username, '[User Email]' => $email], 
            $email);
        return Resp::success(['message' => 'Subscription expired successfully']);
    } else {
        // Handle the case when $subscription->escort or $subscription->escort->user is null
        // For example, you can log an error or return a specific response
        Log::error('Escort or user is null for subscription ' . $subscription->id);
        return Resp::error(['message' => 'Failed to send expiration email']);
    }
}}