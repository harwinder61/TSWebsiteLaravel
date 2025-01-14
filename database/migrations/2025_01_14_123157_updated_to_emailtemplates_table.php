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
        // Insert data into the email_templates table
        DB::table('email_templates')->insert([
            [
                'type' => 'Email_Successful_Change_VERIFIED',
                'title' => 'Your email address change request',
                'subject' => 'Your Email Successfully Changed',
                'content' => '<p>Hello [USER_LOGIN],</p>
                           <p>Your email has been successfully changed and verified.</p>
                           <p>Thank you.</p>
                           <p>Regards,</p>
                           <p>Team Transbunnies</p>
                           <p><strong>THE PLACE TO BE IN!</strong></p>',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This will revert the email template changes, if necessa
            DB::table('email_templates')->whereIn('type', [
                'Email_Successful_Change_VERIFIED',
            ])->delete();   
    
    }
};
