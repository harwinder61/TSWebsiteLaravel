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
    public function up()
    {
        Schema::create('reminder_category', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Insert data into the table
        DB::table('reminder_category')->insert([
            ['name' => 'Branding'],
            ['name' => 'Design'],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('reminder_category');
    }
};
