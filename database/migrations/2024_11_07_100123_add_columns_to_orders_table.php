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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('only_fans_link')->nullable();
            $table->string('many_vids_link')->nullable();
            $table->string('fan_centro_link')->nullable();   //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('only_fans_link');
            $table->dropColumn('many_vids_link');
            $table->dropColumn('fan_centro_link');
            //
        });
    }
};
