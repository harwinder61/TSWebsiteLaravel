<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            DB::table('permissions')->insert([
                ['title' => 'This user can Approve/Reject Forum comments','type'=>'user'],
                ['title' => 'This user can Approve/Reject reviews','type'=>'user'],
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
         db::table('permissions')->where('title', 'This user can Approve/Reject Forum comments')->delete();
         db::table('permissions')->where('title', 'This user can Approve/Reject reviews')->delete();
        });
    }
};
