<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            // Update plan P101
            DB::table('plans')->where('code', 'P101')->update([
                'title' => 'TS OF THE WEEK',
                'days' => 7,
                'allowed_user_account' => 5,
                'price' => 10.00,
                'description' => json_encode([
                    "Permanent space on homepage for 7 days",
                    "Rank top of local TS Escort Search",
                    "Clickable Call Me button",
                    "Unlocked Private Gallery"
                ]),
            ]);

            // Update plan P102
            DB::table('plans')->where('code', 'P102')->update([
                'title' => 'VIP TS LOUNGE',
                'days' => 14,
                'allowed_user_account' => 5,
                'price' => 20.00,
                'description' => json_encode([
                    "Headline at the top of homepage carousel for 14 days",
                    "Rank top of local TS Escort Search",
                    "Mobile number displayed",
                    "Unlocked Private Gallery"
                ]),
            ]);

            // Update plan P103
            DB::table('plans')->where('code', 'P103')->update([
                'title' => 'PAY PER VIEW TS GIRL',
                'days' => 14,
                'allowed_user_account' => 5,
                'price' => 30.00,
                'description' => json_encode([
                    "Appear on Paid Per View carousel for 14 days",
                    "Rank top of local TS Escort Search",
                    "Direct link to either your OnlyFans, ManyVids, or FanCentro pages"
                ]),
            ]);

            // Update plan P104
            DB::table('plans')->where('code', 'P104')->update([
                'title' => 'SPOTLIGHT TS GIRL',
                'days' => 14,
                'allowed_user_account' => 5,
                'price' => 40.00,
                'description' => json_encode([
                    "Appear on Spotlight TS Girl section for 28 days",
                    "Unlimited daily Top-Up option (£4)",
                    "Rank top of local TS Escort Search",
                    "Unlocked Private Gallery"
                ]),
            ]);

            // Update plan P105
            DB::table('plans')->where('code', 'P105')->update([
                'title' => 'FEATURED TS GIRL',
                'days' => 3,
                'allowed_user_account' => 5,
                'price' => 50.00,
                'description' => json_encode([
                    "Appear on Featured TS Girls section for 28 days",
                    "Rank on local TS Escort Search",
                    "Unlocked Private Gallery",
                ]),
            ]);

            // Update plan P106
            DB::table('plans')->where('code', 'P106')->update([
                'title' => 'STANDARD TS GIRL',
                'days' => 28,
                'allowed_user_account' => 5,
                'price' => 60.00,
                'description' => json_encode([
                    "Entry level advert for 28 days",
                    "Appear on local TS Escort Search",
                    "Unlocked Private Gallery",
                ]),
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
        });
    }
};
