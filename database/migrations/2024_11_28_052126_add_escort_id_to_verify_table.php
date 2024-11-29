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
            $table->unsignedBigInteger('escort_id');
            $table->foreign('escort_id')->references('id')->on('users');
        });
    }
    
    public function down()
    {
        Schema::table('verify', function (Blueprint $table) {
            $table->dropForeign(['escort_id']);
            $table->dropColumn('escort_id');
        });
    }
    
};
