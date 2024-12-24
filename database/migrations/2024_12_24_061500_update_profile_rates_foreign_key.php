<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('profile_rates', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['escort_id']);
            
            // Add the new foreign key constraint referencing users table
            $table->foreign('escort_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profile_rates', function (Blueprint $table) {
            // Drop the new foreign key constraint
            $table->dropForeign(['escort_id']);
            
            // Restore the original foreign key constraint
            $table->foreign('escort_id')
                  ->references('id')
                  ->on('profile')
                  ->onDelete('cascade');
        });
    }
};
