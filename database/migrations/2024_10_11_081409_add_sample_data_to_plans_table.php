<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            //
            // Inserting sample data
        DB::table('plans')->insert([
            [
                'title' => 'Basic Plan',
                'code' => 'P101',
                'days' => 30,
                'allowed_user_account' => 5,
                'price' => 10.00,
                'description' => json_encode(['description' => 'This is a basic plan']),
            ],
            [
                'title' => 'Standard Plan',
                'code' => 'P102',
                'days' => 60,
                'allowed_user_account' => 5,
                'price' => 20.00,
                'description' => json_encode(['description' => 'This is a standard plan']),
            ],
            [
                'title' => 'Premium Plan',
                'code' => 'P103',
                'days' => 90,
                'allowed_user_account' => 5,
                'price' => 30.00,
                'description' => json_encode(['description' => 'This is a premium plan']),
            ],
            [
                'title' => 'VIP Plan',
                'code' => 'P104',
                'days' => 180,
                'allowed_user_account' => 5,
                'price' => 40.00,
                'description' => json_encode(['description' => 'This is a vip plan']),
            ],
            [
                'title' => 'Elite Plan',
                'code' => 'P105',
                'days' => 360,
                'allowed_user_account' => 5,
                'price' => 50.00,
                'description' => json_encode(['description' => 'This is a elite plan']),
            ],
            [
                'title' => 'Ultimate Plan',
                'code' => 'P106',
                'days' => 720,
                'allowed_user_account' => 5,
                'price' => 60.00,
                'description' => json_encode(['description' => 'This is a ultimate plan']),
            ]
        ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            //
            // Optionally, you can remove the sample data in the down method
            DB::table('plans')->truncate();

        });
    }
};
