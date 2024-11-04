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
            $table->boolean('allow_whatsapp')->default(false);
            $table->bigInteger('whatsapp_number')->nullable();
            $table->integer('country_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profile', function (Blueprint $table) {
            $table->dropColumn('allow_whatsapp');
            $table->dropColumn('whatsapp_number');
            $table->dropColumn('country_code');
        });
    }
};
