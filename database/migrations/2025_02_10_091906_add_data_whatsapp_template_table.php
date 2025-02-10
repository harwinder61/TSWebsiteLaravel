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
        Schema::table('whatsapp_templates', function (Blueprint $table) {
            DB::table('whatsapp_templates')->insert([
                'type' => 'admin_new_user_added',
                'content' => '<p>Hello [USER_LOGIN],</p>
                <p>Welcome to Transbunnies.com!</p>
                <p>Please <a href="[VERIFIED_EMAIL_LINK]" target="_blank" style="background-color: #c00; color: #fff; font-family: Heebo; padding: 8px 12px; text-decoration: none; border-radius: 5px;">click here</a> to verify your email address and start placing your adverts.</p>
                <p>This link is valid for the next 24 hours only.</p>
                <p>Thank you.</p>',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_templates', function (Blueprint $table) {
            DB::table('whatsapp_templates')->where('type', 'admin_new_user_added')->delete();
        });
    }
};
