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
        $sampleData = [
            [
                'key' => 'ADS_PAGE_IMG',
                'value' => json_encode(['European', 'African', 'Asian']),
            ],  
            [
                'key' => 'ACCOUNT_PAGE_IMG',
                'value' => json_encode(['European', 'African', 'Asian']),
            ],
        ];
        
        foreach ($sampleData as $data) {
            DB::table('settings')->insert($data);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->where('key', 'ADS_PAGE_IMG')->delete();
        DB::table('settings')->where('key', 'ACCOUNT_PAGE_IMG')->delete();
    }
};