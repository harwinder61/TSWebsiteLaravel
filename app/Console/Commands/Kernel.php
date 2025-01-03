<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\ScheduledEmails;

class Kernel extends ConsoleKernel
{
    // Register the custom command
    protected $commands = [
        ScheduledEmails::class,  // Register the command here
    ];

    protected function schedule(Schedule $schedule)
    {
        // Example of scheduling the command to run daily at 2:00 AM
        $schedule->command('app:scheduled-emails')->dailyAt('02:00');
    }
}
