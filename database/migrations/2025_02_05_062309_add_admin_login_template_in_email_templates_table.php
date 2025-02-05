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
        Schema::table('email_templates', function (Blueprint $table) {
            DB::table('email_templates')->insert([
        [
        'type' => 'ts_admin_welcome',
        'subject' => 'Welcome to Transbunnies Admin Panel!',
        'title' => 'Welcome to Transbunnies Admin Panel!',
        'content' => '<p>Hello, [ADMIN_NAME]</p>
                      <p>Welcome to the Transbunnies Admin Panel! We are excited to have you on board as part of our team.</p>
                      <p>Below are some important links to help you get started:</p>
                      <p><strong>1. Login to your account:</strong></p>
                      <p><a href="[LOGIN_URL]" style="background-color: #c00; color: #fff; font-family: Heebo, sans-serif; padding: 8px 12px; text-decoration: none; border-radius: 5px;">Login Now</a></p>
                      <p><strong>2. Forgot your password?</strong></p>
                      <p><a href="[RESET_PASSWORD_URL]" style="background-color: #007bff; color: #fff; font-family: Heebo, sans-serif; padding: 8px 12px; text-decoration: none; border-radius: 5px;">Reset Password</a></p>
                      <p>If you have any questions or need assistance, feel free to <a href="https://phpstack-1347729-5054013.cloudwaysapps.com/ts-contact" style="color: #c00; text-decoration: underline;">contact support</a>.</p>
                      <p> </p>
                      <p>Thank you for joining us!</p>
                      <p> </p>
                      <p>Regards,</p>
                      <p>Team Transbunnies</p>
                      <p><strong>THE PLACE TO BE IN!</strong></p>',
        'status' => true,
        'created_at' => now(),
        'updated_at' => now(),
        ]
        ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            DB::table('email_templates')->where('type', 'ts_admin_welcome')->delete();
        });
    }
};
