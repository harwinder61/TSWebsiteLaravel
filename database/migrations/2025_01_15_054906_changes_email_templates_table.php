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
        DB::table('email_templates')->where('type', 'Email_Successful_Change_VERIFIED')->update([
            'type' => 'Email_Successful_Change_VERIFIED',
            'title' => 'Your email address change request',
            'subject' => 'Your Email Successfully Changed',
            'content' => '<p>Hello [USER_LOGIN],</p>
                   <p>Your email has been successfully changed and verified.</p>
                   <p> Please confirm your email <a href="https://phpstack-1347729-5054013.cloudwaysapps.com/confirm-email?token=[USER_TOKEN]" style="background-color: #c00; color: #fff; font-family: Heebo, sans-serif; padding: 8px 12px; text-decoration: none; border-radius: 5px;">Click here</a>
</p>
                   <p>Thank you.</p>
                   <p>Regards,</p>
                   <p>Team Transbunnies</p>
                   <p><strong>THE PLACE TO BE IN!</strong></p>',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('email_templates')->where('type', 'Email_Change_Request')->update([
            'subject' => 'Your email address change request',
            'title' => 'Your email address change request',
            'content' => '<p>Hello, [USER_LOGIN],</p>
            <p> </p>
            <p>As requested, your current email address will be changed on Transbunnies.com once your new email address, [RESET_EMAIL] is verified. <br>
            Please note that once [RESET_EMAIL] is verified, you will no longer receive further correspondence on this current email address or be able to login with it.</p>
            <p>If you did not request this change, please <a href="https://phpstack-1347729-5054013.cloudwaysapps.com/ts-contact " style="background-color: #c00; color: #fff; font-family: Heebo, sans-serif; padding: 8px 12px; text-decoration: none; border-radius: 5px;">click here</a> to contact support.</p>
            <p> </p>
            <p>Thank you.</p>
            <p> </p>
            <p>Regards,</p>
            <p>Team Transbunnies</p>
            <p><strong>THE PLACE TO BE IN!</strong></p>',
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('email_templates')->where('type', 'Email_Successful_Change_VERIFIED')->delete();
        DB::table('email_templates')->where('type', 'Email_Change_Request')->delete();
    }
};
