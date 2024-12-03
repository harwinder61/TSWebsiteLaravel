<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */ public function up(): void
    {
        Schema::table('blog', function (Blueprint $table) {
            // Adding a slug column that is nullable and unique
            $table->string('slug')->nullable()->unique();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('blog', function (Blueprint $table) {
            // Dropping the slug column if the migration is rolled back
            $table->dropColumn('slug');
        });
    }
};
