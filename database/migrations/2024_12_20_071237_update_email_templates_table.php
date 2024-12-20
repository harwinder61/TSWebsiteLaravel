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
        $title = [
            'Transbunnies Email Reset Notification',
            'Transbunnies New Password Notification',
            'New order',
            'Flash Email confirmation',
            'Subscription_Expired',
            '24hrs Ad Expiry Notification',
            '4 weeks of profile Inactivity Notification',
            'Great News! You are a step away to place your FREE Featured Ad',
            'Verify your new email address',
            'Account Deleted',
        ];
    
        foreach ($title as $title) {
            DB::table('email_templates')->insert([
                'title' => $title
            ]);
        }
    }
};
