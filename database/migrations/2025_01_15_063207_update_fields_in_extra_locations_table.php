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
        Schema::table('extra_locations', function (Blueprint $table) {
             // Drop the existing foreign key constraint
             $table->dropForeign(['subscription_id']);
             // Add the foreign key constraint with onDelete cascade
             $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('cascade')->nullable()->change();
 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('extra_locations', function (Blueprint $table) {
             // Drop the foreign key constraint
             $table->dropForeign(['subscription_id']);
             // Re-add the foreign key constraint without onDelete cascade
             $table->foreign('subscription_id')->references('id')->on('subscriptions')->nullable()->change();
 
        });
    }
};
