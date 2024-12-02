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
                ['title' => 'This user can add new content','type'=>'content'],
                ['title' => 'This user can edit content','type'=>'content'],
                ['title' => 'This user can delete content','type'=>'content'],
                ['title' => 'This user can view content','type'=>'content'],
                // Add more permissions as needed
            ]); 

            DB::table('permissions')
            ->whereNull('type')
            ->update(['type' => 'user']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            //
            DB::table('permissions')->whereIn('title', [
                'This user can add new content',
                'This user can edit content',
                'This user can delete content',
                'This user can view content'
            ])->delete();

        });
    }
};
