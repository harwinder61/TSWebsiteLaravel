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
        Schema::create('profile', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId("escort_id")->constrained("users")->onDelete("cascade");
            $table->integer('phone_number')->nullable();
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('orientation')->nullable();
            $table->string('ethnicity')->nullable();
            $table->integer('height')->nullable();
            $table->integer('weight')->nullable();
            $table->string('hair')->nullable();
            $table->string('eyes')->nullable();
            $table->integer('breasts_size')->nullable();
            $table->string('breasts_cup')->nullable();
            $table->string('butt')->nullable();
            $table->string('body')->nullable();
            $table->integer('cock_size')->nullable();
            $table->json('languages')->nullable();
            $table->json("offer_services_to")->nullable();
            $table->string('location')->nullable();

            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile');
    }
};
