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
        Schema::table('plans', function (Blueprint $table) {
            $table->integer('advert_spaces')->nullable();
            $table->string('checkout_text')->nullable();
            $table->string('desktop_placeholder')->nullable();
            $table->string('mobile_placeholder')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {    
            $table->dropColumn('advert_spaces');
            $table->dropColumn('checkout_text');
            $table->dropColumn('desktop_placeholder');
            $table->dropColumn('mobile_placeholder');
        });
    }
};
