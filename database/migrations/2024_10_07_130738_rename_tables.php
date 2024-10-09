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
        Schema::rename('_region', 'locations_regions');
        Schema::rename('cities', 'locations_cities');
        Schema::rename('countries', 'locations_countries');
        Schema::rename('nationality', 'locations_nationalities');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('locations_regions', '_region');
        Schema::rename('locations_cities', 'cities');
        Schema::rename('locations_countries', 'countries');
        Schema::rename('locations_nationalities', 'nationality'); 
    }






};
