<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;

class DeleteExpiredUsers extends Command
{
   
    protected $signature = 'users:delete-expired';
    protected $description = 'Delete users marked as deleted for more than 30 days';
    public function handle()
    {
        $thresholdDate = now()->subDays(30);
        
        // Delete users marked as deleted for more than 30 days
        $deletedUsersCount = User::where('is_delete', 1)
            ->where('deleted_on', '<', $thresholdDate) // Use 'deleted_at' instead of 'deleted_on'
            ->delete();
    
        $this->info("Deleted {$deletedUsersCount} expired users successfully.");
    }
}
