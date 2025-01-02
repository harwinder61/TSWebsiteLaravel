<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\BaseSubscription;
use App\Models\User;
use App\Mail\EmailHelper;
use App\Services\Resp;



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

    // Command logic// Command logic
    public function handle()
    {
        $expiredSubscriptions = BaseSubscription::where('end_date', '<=', now()->subHours(24))
            ->where('status', 'active')
            ->get();
    
        foreach ($expiredSubscriptions as $subscription) {
            // Send an email to the user with the expired subscription
            $this->sendExpirationEmail($subscription->escort_id);
    
            // Update the subscription status to 'expired'
            $subscription->status = 'expired';
            $subscription->save();
        }
    
        // Log the expired subscriptions
        Log::info('Expired subscriptions:', $expiredSubscriptions);
    }
    
    // New method to send an email to the user with the expired subscription
    private function sendExpirationEmail($escortId)
    {
        $user = User::find($escortId);
        $email = $user->email;
        EmailHelper::sendDynamicEmail('subscription_expired', 
        ['[USER_LOGIN]' => $user->username, '[USER_EMAIL]' => $user->email], 
        $user->email);
        return Resp::success(['message' => 'Subscription expired successfully']);
    }
}
