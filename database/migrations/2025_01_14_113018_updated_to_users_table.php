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
        // Update the 'email_templates' table for the 'Email_Change_Request' type
        DB::table('email_templates')->where('type', 'Email_Change_Request')->update([
            'subject' => 'Your email address change request',
            'content' => '<p>Hello, [USER_LOGIN],</p>
            <p> </p>
            <p>As requested, your current email address will be changed on Transbunnies.com once your new email address, [RESET_EMAIL] is verified. <br>
            Please note that once [RESET_EMAIL] is verified, you will no longer receive further correspondence on this current email address or be able to login with it.</p>
            <p>If you did not request this change, please <a href="https://phpstack-1347729-5054013.cloudwaysapps.com/ts-contact">click here</a> to contact support.</p>
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
        // This will revert the email template changes, if necessa
            DB::table('email_templates')->whereIn('type', [
                'Email_Change_Request',
        ]);
    }
};
