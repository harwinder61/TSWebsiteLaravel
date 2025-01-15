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
        Schema::create('extra_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained("subscriptions")->nullable();
            $table->foreignId("region_id")->constrained("locations")->nullable();
            $table->foreignId("county_id")->constrained("locations")->nullable();
            $table->foreignId("city_id")->constrained("locations")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extra_locations');
    }
};
