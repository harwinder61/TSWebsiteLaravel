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
        Schema::table('settings', function (Blueprint $table) {
            //
        });

        $sampleData = [
            [
                'key' => 'extra_services',
                'value' => json_encode(['CUM','DTF','Filming','Finger/Fisting(Giving)','Finger/Fisting(Receiving)','OWO','PSE','SWallow','CIM','Facial','Foot Worship','Prostate Massage','Rimming(Giving)','Water Sports']),
            ],
            [
                'key'=>'languages',
                'value'=>json_encode(["English",'Chinese','Spanish','German','Portuguese','French','Italian','Russian','Japanese','Other']),
            ]
            
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
        Schema::table('settings', function (Blueprint $table) {
            //
        });
    }
};
