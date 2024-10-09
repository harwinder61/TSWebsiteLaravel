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
            $table->boolean("has_twitter")->nullable();
            $table->boolean("has_snapchat")->nullable();
            $table->boolean("has_instagram")->nullable();
            $table->boolean("has_tiktok")->nullable();
            $table->string("twitter_handle")->nullable();
            $table->string("snapchat_handle")->nullable();
            $table->string("instagram_handle")->nullable();
            $table->string("tiktok_handle")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profile', function (Blueprint $table) {
            //
        });
    }
};
