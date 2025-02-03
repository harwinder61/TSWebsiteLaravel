<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\Media;

class DeleteExpiredUsers extends Command
{
   
    protected $signature = 'users:delete-expired';
    protected $description = 'Delete users marked as deleted for more than 30 days';
    public function handle()
    {
        $thresholdDate = now()->subDays(30);
        
        // Log the threshold date
        Log::info("Threshold date: {$thresholdDate}");
        
        // Retrieve users marked as deleted for more than 30 days
        $users = User::where('is_delete', 1)
            ->where('delete_on', '<', $thresholdDate)
            ->get(); // Get the collection of users

        // Log the IDs of users to be deleted
        Log::info("Users to delete: " . $users->pluck('id')->toJson());
    
        // Delete related media records for each user
        foreach ($users as $user) {
            Media::where('escort_id', $user->id)->delete();
        }
    
        // Now delete the users
        $deletedUsersCount = $users->count();
        User::where('is_delete', 1)
            ->where('delete_on', '<', $thresholdDate)
            ->delete();
    
        Log::info("Number of users deleted: {$deletedUsersCount}");
        $this->info("Deleted {$deletedUsersCount} expired users successfully.");
    }
}


