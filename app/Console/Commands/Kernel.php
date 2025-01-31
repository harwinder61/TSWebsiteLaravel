<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\ScheduledEmails;
use App\Console\Commands\DeleteExpiredUsers;

class Kernel extends ConsoleKernel
{
    // Register the custom command
    protected $commands = [
        ScheduledEmails::class,  // Register the command here
        DeleteExpiredUsers::class,
    ];
// app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
    // Schedule your command to run daily (or at whatever interval you need)
    $schedule->command('app:scheduled-emails')->daily();
    $schedule->command('users:delete-expired')->daily();
}


}
