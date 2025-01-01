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
            DB::table('plans')->where('code', 'P101')->update(['allowed_user_account' => 100]);
            DB::table('plans')->where('code', 'P102')->update(['allowed_user_account' => 100]);
            DB::table('plans')->where('code', 'P103')->update(['allowed_user_account' => 100]);
            DB::table('plans')->where('code', 'P104')->update(['allowed_user_account' => 100]);
            DB::table('plans')->where('code', 'P105')->update(['allowed_user_account' => 100]);
            DB::table('plans')->where('code', 'P106')->update(['allowed_user_account' => 100]);
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
        DB::table('plans')->where('code', 'P101')->update(['allowed_user_account' => 10]);
        DB::table('plans')->where('code', 'P102')->update(['allowed_user_account' => 15]);
        DB::table('plans')->where('code', 'P103')->update(['allowed_user_account' => 20]);
        DB::table('plans')->where('code', 'P104')->update(['allowed_user_account' => 25]);
        DB::table('plans')->where('code', 'P105')->update(['allowed_user_account' =>30]);
        DB::table('plans')->where('code', 'P106')->update(['allowed_user_account' => 35]);

        });
    }
};
