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
        Schema::create('reminder_comment', function (Blueprint $table) {
            $table->string('reminder_comment')->nullable();
            $table->foreignId('reminder_id')->constrained('reminder');
            $table->foreignId('admin_id')->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
      Schema::dropIfExists('reminder_comment');
    }
};
