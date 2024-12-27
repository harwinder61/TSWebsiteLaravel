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
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('value_mobile');
            $table->dropColumn('value_desktop');
            $table->dropColumn('type');
            $table->string('key')->nullable();
            $table->json('value')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('value_mobile');
            $table->string('value_desktop');
            $table->string('type');
            $table->dropColumn('key');
            $table->dropColumn('value');
        });
    }
};
