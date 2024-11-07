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
            $table->boolean('has_onlyfans')->nullable();
            $table->boolean('has_manyvids')->nullable();
            $table->boolean('has_fancentro')->nullable();
            $table->string('onlyfans_handle')->nullable();
            $table->string('manyvids_handle')->nullable();
            $table->string('fancentro_handle')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profile', function (Blueprint $table) {
            $table->dropColumn('has_onlyfans');
            $table->dropColumn('has_manyvids');
            $table->dropColumn('has_fancentro');
            $table->dropColumn('onlyfans_handle');
            $table->dropColumn('manyvids_handle');
            $table->dropColumn('fancentro_handle');
        });
    }
};
