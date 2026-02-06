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
        DB::statement('
            DELETE t1
            FROM settings t1
            INNER JOIN settings t2 
                ON t1.`key` = t2.`key` AND t1.id > t2.id
        ');

        Schema::table('settings', function (Blueprint $table) {
            $table->unique('key');
        });
        $sampleData = [
            [
                'key' => 'ADS_PAGE_IMG',
                'value' => 1,
            ],  
            [
                'key' => 'ACCOUNT_PAGE_IMG',
                'value' => 1,
            ],
        ];
        
        foreach ($sampleData as $data) {
            DB::table('settings')->updateOrInsert(
                ['key' => $data['key']], // Assuming 'key' is the unique identifier
                $data
            );
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique(['key']);
        });
    }
};
