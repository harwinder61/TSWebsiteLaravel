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
                ['title' => 'This user can add users'],
                ['title' => 'This user can update users'],
                ['title' => 'This user can delete users'],
                ['title' => 'This user can view users'],
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            DB::table('permissions')->whereIn('title', [
                'This user can add users',
                'This user can update users',
                'This user can delete users',
                'This user can view users',
            ])->delete();
        });
    }
};