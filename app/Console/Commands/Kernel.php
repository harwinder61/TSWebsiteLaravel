<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\ScheduledEmails;
use App\Console\Commands\DeleteExpiredUsers;
use App\Console\Commands\ScheduledSms;
use App\Console\Commands\ScheduledAdvertEnded;
use App\Console\Commands\ScheduledInactive;
use Illuminate\Support\Facades\Log;
use App\Models\AuthUser;
use App\Helpers\SmsHelper;
use App\Models\SmsLogs;
use Carbon\Carbon;
use Twilio\Rest\Client;
// use App\Console\Commands\ScheduledSms;

class Kernel extends ConsoleKernel
{
    // Register the custom command
    protected $commands = [
        ScheduledEmails::class,  // Register the command here
        DeleteExpiredUsers::class,
        ScheduledSms::class,
        ScheduledAdvertEnded::class,
        ScheduledInactive::class
    ];
// app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
    // Schedule your command to run daily (or at whatever interval you need)
    $schedule->command('app:scheduled-emails')->daily();
    $schedule->command('users:delete-expired')->daily();
    $schedule->command('app:scheduled-sms')->daily();
    $schedule->command('app:scheduled-advert-ended')->daily();
    $schedule->command('app:scheduled-inactive-users')->daily();
}


}
