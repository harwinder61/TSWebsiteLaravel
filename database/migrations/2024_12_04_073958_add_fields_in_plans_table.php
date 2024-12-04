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
        Schema::table('plans', function (Blueprint $table) {
            $table->integer('available_slots')->nullable();
        });


        DB::table('plans')->where('code', 'P101')->update([
            'available_slots' => 5
        ]);
        
        
        DB::table('plans')->where('code', 'P102')->update([
            'available_slots' => 10
        ]);
        DB::table('plans')->where('code', 'P103')->update([
            'available_slots' => 15
        ]);
        DB::table('plans')->where('code', 'P104')->update([
            'available_slots' => 20
        ]);
        DB::table('plans')->where('code', 'P105')->update([
            'available_slots' => 25
        ]);
        DB::table('plans')->where('code', 'P106')->update([
            'available_slots' => 30
        ]);
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('available_slots');
        });
    }
};
