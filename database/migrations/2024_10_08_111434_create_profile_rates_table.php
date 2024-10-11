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
        Schema::create('profile_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('escort_id')->constrained('profile')->onDelete('cascade');

            $table->string('category');
            $table->float('15_min');
            $table->float('30_min');
            $table->float('1_hour');
            $table->float('2_hour');
            $table->float('4_hour');
            $table->float('overnight');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_rates');
    }
};
