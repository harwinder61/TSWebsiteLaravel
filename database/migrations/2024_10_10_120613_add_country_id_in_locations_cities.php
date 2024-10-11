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
        Schema::table('locations_cities', function (Blueprint $table) {
            //
            $table->foreignId('country_id')->constrained('locations_countries')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations_cities', function (Blueprint $table) {
            //
            $table->dropForeign(['country_id']);
            $table->dropColumn('country_id');
        });
    }
};
