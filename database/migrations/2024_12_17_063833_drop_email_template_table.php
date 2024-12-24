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
        Schema::dropIfExists('email_template');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('email_template', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('subject');
            $table->text('message');
            $table->timestamps();
        });
    }
};
