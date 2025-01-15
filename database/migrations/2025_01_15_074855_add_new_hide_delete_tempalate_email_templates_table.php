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
        DB::table('email_templates')->insert([
            [
                'type' => 'Hide_Profile',
                'subject' => 'Your profile hide request',
                'title' => 'Your profile hide request',
                'content' => '<p>Hello, [USER_LOGIN]</p>
                          <p>As requested, your profile will be hidden on Transbunnies.com. Please note that this action will make your profile invisible to other users, but your account will remain active, and you can still log in and access your data.</p>
                          <p>If you did not request this change, please <a href="https://phpstack-1347729-5054013.cloudwaysapps.com/ts-contact" style="background-color: #c00; color: #fff; font-family: Heebo, sans-serif; padding: 8px 12px; text-decoration: none; border-radius: 5px;">click here</a> to contact support.</p>
                          <p> </p>
                          <p>Thank you.</p>
                          <p> </p>
                          <p>Regards,</p>
                          <p>Team Transbunnies</p>
                          <p><strong>THE PLACE TO BE IN!</strong></p>',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'Delete_Profile',
                'subject' => 'Your profile deletion request',
                'title' => 'Your profile deletion request',
                'content' => '<p>Hello, [USER_LOGIN]</p>
                          <p> </p>
                          <p>As requested, your profile will be permanently deleted from Transbunnies.com. Please note that this action is irreversible, and all your account data will be removed.</p>
                          <p>If you did not request this change, please <a href="https://phpstack-1347729-5054013.cloudwaysapps.com/ts-contact" style="background-color: #c00; color: #fff; font-family: Heebo, sans-serif; padding: 8px 12px; text-decoration: none; border-radius: 5px;">click here</a> to contact support.</p>
                          <p> </p>
                          <p>Thank you.</p>
                          <p> </p>
                          <p>Regards,</p>
                          <p>Team Transbunnies</p>
                          <p><strong>THE PLACE TO BE IN!</strong></p>',
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('email_templates')->where('type', 'Email_Successful_Change_VERIFIED')->update([
            'type' => 'Email_Successful_Change_VERIFIED',
            'title' => 'Your email address change request',
            'subject' => 'Your Email Successfully Changed',
            'content' => '<p>Hello [USER_LOGIN],</p>
                   <p>Your email has been successfully changed and verified.</p>
                   <p> Please confirm your email <a href="https://phpstack-1347729-5054013.cloudwaysapps.com/confirm/email?token=[USER_TOKEN]/reset" style="background-color: #c00; color: #fff; font-family: Heebo, sans-serif; padding: 8px 12px; text-decoration: none; border-radius: 5px;">Click here</a>
</p>
                   <p>Thank you.</p>
                   <p>Regards,</p>
                   <p>Team Transbunnies</p>
                   <p><strong>THE PLACE TO BE IN!</strong></p>',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
