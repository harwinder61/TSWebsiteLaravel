<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->integer('photo_accuracy')->nullable();
            $table->integer('service')->nullable();
            $table->integer('clean_liness')->nullable();
            $table->integer('location')->nullable();
            $table->integer('value_for_money')->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
