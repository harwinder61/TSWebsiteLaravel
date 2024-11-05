<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('created_by');
            // Change the column to a foreignId (nullable)
            $table->foreignId('created_by')->nullable()->constrained('users')->change();

        });
    }

    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            
            // Change the column back to an integer (non-nullable in this example)
            $table->integer('created_by')->nullable()->change();
        });
    }
};
