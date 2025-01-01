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
        // Sample data for the settings table


// You can insert this data into the settings table in your migration's up() method
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->timestamps();
        });

        $sampleData = [
            [
                'key' => 'gender',
                'value' => json_encode(['Male', 'Female', 'Transgender', 'Non-binary', 'Genderqueer']),
            ],
            [
                'key' => 'orientation',
                'value' => json_encode(['Heterosexual', 'Homosexual', 'Bisexual', 'Pansexual', 'Asexual']),
            ],
            [
                'key' => 'nationality',
                'value' => json_encode(['American', 'Canadian', 'British', 'Australian', 'Other']),
            ],
            [
                'key' => 'eyes',
                'value' => json_encode(['Brown', 'Blue', 'Green', 'Hazel', 'Gray']),
            ],
            [
                'key' => 'butt',
                'value' => json_encode(['Small', 'Medium', 'Large']),
            ],
            [
                'key' => 'cock_size',
                'value' => json_encode(['Small', 'Medium', 'Large']),
            ],
            [
                'key' => 'breasts_size',
                'value' => json_encode(['A', 'B', 'C', 'D', 'DD']),
            ],
            [
                'key' => 'breasts_cup',
                'value' => json_encode(['A', 'B', 'C', 'D', 'DD']),
            ],
            [
                'key' => 'body',
                'value' => json_encode(['Slim', 'Athletic', 'Curvy', 'Plus Size']),
            ],
            [
                'key' => 'hair',
                'value' => json_encode(['Black', 'Brown', 'Blonde', 'Red', 'Gray']),
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
        Schema::table('settings', function (Blueprint $table) {

        });
        DB::table('settings')->truncate();
    }
};
