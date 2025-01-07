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
}
// public function updateLastActiveAt($user_id)
// {
//     $user = User::find($user_id);
//     $user->last_active_at = Carbon::now();
//     $user->save();
// }



// public function sendInactivityEmails()
// {
//     // Fetch users who haven't been active for the last 28 days or have no last active timestamp.
//     $inactiveUsers = User::where(function ($query) {
//         $query->where('last_active_at', '<', Carbon::now()->subDays(28)->startOfDay())
//               ->orWhereNull('last_active_at');
//     })
//     ->where('drop_mail', 0)
//     ->get();
    
//     // Log how many inactive users were found.
//     Log::info('Found ' . $inactiveUsers->count() . ' inactive users');

//     $count = 1;

//     // Loop through all inactive users to send them emails.
//     foreach ($inactiveUsers as $user) {
//         // Log which user will receive the email.
//         Log::info('Sending email to user ' . $count.' >>> '.$user->id . ' with email ' . $user->email);

//         try {
//             // Send the inactivity email to the user.
//             EmailHelper::sendDynamicEmail('4 weeks of profile inactivity',
//                 ['[User Login]' => $user->username, '[User Email]' => $user->email],
//                 $user->email);
//             Log::info('Email sent to: ' . $user->email);
//         } catch (\Exception $e) {
//             Log::error('Failed to send email to ' . $user->email . ': ' . $e->getMessage());
//         }

//         Log::info('Updating last_active_at for user ' . $user->id . ' to: ' . Carbon::now()->subDays(29));
//         $this->updateLastActiveAt($user->id);


//         $user->drop_mail = 1;
//         if($user->last_active_at == null){
//             $user->last_active_at = Carbon::now()->subDays(29);
//         }
//         $user->save(); 

//         $count++;  
//     }
// }

public function sendInactivityEmails()
{
    // Fetch users who haven't been active for the last 28 days or have no last active timestamp.
    $inactiveUsers = User::where(function ($query) {
        $query->where('last_active_at', Carbon::now()->subDays(28)->startOfDay())
              ->orWhereNull('last_active_at');
    })
    ->where('drop_mail', 0)
    ->get();
    
    // Log how many inactive users were found.
    Log::info('Found ' . $inactiveUsers->count() . ' inactive users');

    $count = 1;

    // Loop through all inactive users to send them emails.
    foreach ($inactiveUsers as $user) {
        // Log which user will receive the email.
        Log::info('Sending email to user ' . $count.' >>> '.$user->id . ' with email ' . $user->email);

        try {
            // Send the inactivity email to the user.
            EmailHelper::sendDynamicEmail('4 weeks of profile inactivity',
                ['[User Login]' => $user->username, '[User Email]' => $user->email],
                $user->email);
            Log::info('Email sent to: ' . $user->email);

            // Update last_active_at to current time after sending email
            $user->last_active_at = Carbon::now();
            $user->drop_mail = 1;
            $user->save(); 

            Log::info('Updated last_active_at for user ' . $user->id . ' to: ' . $user->last_active_at);
        } catch (\Exception $e) {
            Log::error('Failed to send email to ' . $user->email . ': ' . $e->getMessage());
        }

        $count++;  
    }
}


}



