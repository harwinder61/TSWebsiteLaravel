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
        Schema::create('profile', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId("escort_id")->constrained("users")->onDelete("cascade");
            //$table->string("action");
            //$table->string("account_type");
            //$table->string("register");
            //$table->string("username");
            //$table->integer("ad_lvl_0");
            //$table->integer("ad_lvl_1");
            //$table->integer("ad_lvl_2");
            //$table->integer("registration_phone");
            //$table->integer("day_birth");
            //$table->string("month_birth");
            //$table->integer("year_birth");
            //$table->string("gender");
            //$table->string("orientation");
            //$table->string("ethnicity");
            //$table->string("nationality");
            //$table->string("height");
            //$table->string("weight");
            //$table->string("hair");
            //$table->string("eyes");
            //$table->integer("breasts_size");
            //$table->string("breasts_cup");
            //$table->string("butt");
            //$table->string("body");
            //$table->integer("cock_size");
            //$table->string("onlyfans_link");
            //$table->string("twitter_link");
            //$table->string("allow_whatsapp_contact");
            //$table->string("agreement");
            
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile');
    }
};
