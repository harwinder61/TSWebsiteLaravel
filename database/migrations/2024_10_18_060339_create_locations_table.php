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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('parent_id')->nullable();
            $table->string('type');
            $table->string('slug');
            $table->timestamps();
        });

        $data = file_get_contents(__DIR__.'/locations_data.json');
        $locations = json_decode($data, true);

        DB::table('locations')->insert($locations);
    }


    public function down(): void
    {
        Schema::dropIfExists('locations');
    }


};
