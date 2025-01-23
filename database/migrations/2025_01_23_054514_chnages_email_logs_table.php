<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_logs', function (Blueprint $table) {
            // Alter the 'message' column to LONGTEXT
            $table->longText('message')->change();
            
            // Ensure 'subject' and 'to' columns remain as VARCHAR
            $table->string('subject')->change(); // If needed, specify the length
            $table->string('to')->change(); // If needed, specify the length
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_logs', function (Blueprint $table) {
            // Rollback the column changes to the original types
            $table->string('message')->change();
            $table->string('subject')->change();
            $table->string('to')->change();
        });
    }
};
