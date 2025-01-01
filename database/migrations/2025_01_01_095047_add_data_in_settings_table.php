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
        //Schema::table('settings', function (Blueprint $table) {
            $sampleData = [
                [
                    'key' => 'ethnicity',
                    'value' => json_encode(['European', 'African', 'Asian']),
                ],
                [
                    'key' => 'P101',
                    'value' => 1,
                ],
                [
                    'key' => 'P102',
                    'value' => 1,
                ],
                [
                    'key' => 'P103',
                    'value' => 1,
                ],
                [
                    'key' => 'P104',
                    'value' => 1,
                ],
                [
                    'key' => 'P105',
                    'value' => 1,
                ],
                [
                    'key' => 'P106',
                    'value' => 1,
                ],
                [
                    'key' => 'TSWEEK',
                    'value' => 1,
                ],
                [
                    'key' => 'PPV',
                    'value' => 1,
                ],
                
            ];
            foreach ($sampleData as $data) {
                DB::table('settings')->insert($data);
            }
        //});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            //
        });
    }
};
