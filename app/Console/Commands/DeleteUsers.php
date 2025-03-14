<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleteUsers extends Command
{
    // The name and signature of the console command.
    protected $signature = 'users:delete-non-type3 {limit? : The number of users to delete}';

    // The console command description.
    protected $description = 'Delete users from the table where user_type is not 3 with an optional limit';

    // Execute the console command.
    public function handle()
    {
        // Get the limit from the command input (defaults to null if not provided)
        $limit = $this->argument('limit') ?? null;

        // Confirm action before deletion
        if ($this->confirm('Do you really want to delete users where user_type is not 3?')) {
            // Begin a database transaction to ensure consistency
            DB::beginTransaction();

            try {
                // Build the query to get users to delete
                $query = User::where('user_type', '!=', 3);

                // Apply the limit if provided
                if ($limit) {
                    $query->limit($limit);
                }

                // Get the users' data (including their usernames) that will be deleted
                $usersToDelete = $query->get(['id', 'username']); // Adjust 'username' to your actual column name

                // If no users match the criteria
                if ($usersToDelete->isEmpty()) {
                    $this->info('No users found to delete.');
                    return;
                }

                // Display the usernames of the users being deleted
                $this->info('Deleting the following users:');
                foreach ($usersToDelete as $user) {
                    $this->line($user->username); // Assuming 'username' is the column for user names
                }

                // Step 1: Delete dependent records in the verify table first
                // Delete dependent verify records where escort_id refers to users to be deleted
                DB::table('verify')->whereIn('escort_id', $usersToDelete->pluck('id')->toArray())->delete();

                // Step 2: Delete dependent records in the subscriptions table
                DB::table('subscriptions')
                    ->whereIn('order_id', function ($query) use ($usersToDelete) {
                        $query->select('id')
                            ->from('orders')
                            ->whereIn('escort_id', $usersToDelete->pluck('id')->toArray());
                    })
                    ->delete();

                // Step 3: Delete dependent records in the sms_logs table (if any)
                DB::table('sms_logs')->whereIn('user_id', $usersToDelete->pluck('id')->toArray())->delete();

                // Step 4: Delete dependent records in the media table (if any)
                DB::table('media')->whereIn('escort_id', $usersToDelete->pluck('id')->toArray())->delete();

                // Step 5: Delete dependent records in the profile_like table (if any)
                DB::table('profile_like')->whereIn('fan_id', $usersToDelete->pluck('id')->toArray())->delete();

                // Step 6: Delete dependent records in the orders table (if any)
                DB::table('orders')->whereIn('escort_id', $usersToDelete->pluck('id')->toArray())->delete();

                // Step 7: Now delete the users
                $deletedCount = $query->delete();

                // Commit the transaction
                DB::commit();

                // Output the result
                $this->info("Deleted {$deletedCount} users where user_type is not 3.");
            } catch (\Exception $e) {
                // Rollback the transaction if anything goes wrong
                DB::rollBack();
                $this->error('Error: ' . $e->getMessage());
            }
        } else {
            $this->info('Action canceled.');
        }
    }
}
