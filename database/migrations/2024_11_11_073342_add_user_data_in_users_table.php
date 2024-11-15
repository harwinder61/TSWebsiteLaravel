<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{

    public function up(): void
    {
        DB::table('users')->insert([
            [
                'username' => 'adminTs_test',
                'email' => 'adminTs0011@yopmail.com',
                'password' => Hash::make('123456789'),
                'user_type' => 3,
                'created_at' => now(),
                'updated_at' => now(),
                'email_verified' => 1,   
            ]
        ]);

    $user = DB::table('users')->where('email', 'adminTs0011@yopmail.com')->first();
    DB::table('profile')->insert([
        [
            'name' => 'adminTs_test',
            'escort_id' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]
    ]);
    }   
};
