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
                'type' => 'Email_Change_Request',
                'subject' => 'Your email address change request',
                'content' => '<p>Hello [USER_LOGIN],</p>
                           <p>As requested, your current email address will be changed on Transbunnies.com once your new email address is verified.</p>
                           <p>Please note that once your new email address is verified, you will no longer receive further correspondence on this current email address or be able to login with it.</p>
                           <p>If you did not request this change, please <a href="https://transbunnies.com/?page_id=330">click here</a> to contact support.</p>
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
        // Rollback data insertions
        //DB::table('email_templates')->where('name', 'Email Change Request')->delete();
    }
};
