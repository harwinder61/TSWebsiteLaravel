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
        Schema::table('sms_templates', function (Blueprint $table) {
            DB::table('sms_templates')->insert([
                [
                    'type' => 'advert_ended_renew',
                    'content' => '<p>Hello [USER_LOGIN],</p>
                    <p>Your advertisement [ADVERT_TITLE] has ended.</p>
                    <p>Please <a href="[ADVERT_LINK]" target="_blank" style="background-color: #c00; color: #fff; font-family: Heebo; padding: 8px 12px; text-decoration: none; border-radius: 5px;">click here</a> to renew your advert.</p>
                    <p>Thank you.</p>',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'type' => 'advert_ending_in_48_hours',
                    'content' => '<p>Hello [USER_LOGIN],</p>
                    <p>Your advertisement [ADVERT_TITLE] is ending in 48 hours.</p>
                    <p>Please <a href="[ADVERT_LINK]" target="_blank" style="background-color: #c00; color: #fff; font-family: Heebo; padding: 8px 12px; text-decoration: none; border-radius: 5px;">click here</a> to extend your advert.</p>
                    <p>Thank you.</p>',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'type' => 'come_back_to_transbunnies_after_14_days',
                    'content' => '<p>Hello [USER_LOGIN],</p>
                    <p>It\'s been 14 days since your ad "[ADVERT_TITLE]" went live. Check your responses and update your ad <a href="[ADVERT_LINK]" target="_blank" style="background-color: #c00; color: #fff; font-family: Heebo; padding: 8px 12px; text-decoration: none; border-radius: 5px;">here</a>.</p>
                    <p>Best regards,<br>Transbunnies Team</p>',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('sms_templates')
            ->whereIn('type', ['advert_ended_renew', 'advert_ending_in_48_hours', 'come_back_to_transbunnies_after_14_days'])
            ->delete();
    }
};
