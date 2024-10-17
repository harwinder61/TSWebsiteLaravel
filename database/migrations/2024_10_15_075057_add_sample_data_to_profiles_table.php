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
        Schema::table('profile', function (Blueprint $table) {
            $table->foreignId('city_id')->nullable();
            $table->foreignId('region_id')->nullable();
            $table->foreignId('country_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('profile', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
            $table->dropForeign(['region_id']);
            $table->dropForeign(['country_id']);
        });
    }
};
