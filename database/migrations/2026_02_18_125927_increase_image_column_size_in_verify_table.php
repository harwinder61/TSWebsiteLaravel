<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('verify', function (Blueprint $table) {
            // 'text' can hold 65,535 characters, more than enough for these URLs
            $table->text('selfie_image')->nullable()->change();
            $table->text('passport_image')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('verify', function (Blueprint $table) {
            $table->string('selfie_image', 255)->nullable()->change();
            $table->string('passport_image', 255)->nullable()->change();
        });
    }
};
