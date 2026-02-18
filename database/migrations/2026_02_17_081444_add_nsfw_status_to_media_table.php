<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNsfwStatusToMediaTable extends Migration
{
    public function up()
    {
        Schema::table('media', function (Blueprint $table) {
            $table->string('nsfw_status')->default('unapproved')->after('path');
        });
    }

    public function down()
{
    Schema::table('media', function (Blueprint $table) {
        if (Schema::hasColumn('media', 'nsfw_status')) {
            $table->dropColumn('nsfw_status');
        }
    });
}
}