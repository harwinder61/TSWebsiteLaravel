<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    
    public function up()
    {
        DB::table('email_templates')->insert([
            'subject' => 'A New Review Added',
            'type' => 'a_new_review_added',
            'title' => 'A New Review Added',
            'content' => '<p>[fAN_NAME] has just added a new review for [ESCORT_NAME] </p>
                        <p>please log in to the admin panel to view the review</p>
                        <p>Regards,</p>
                        <p>Team Transbunnies</p>
                        <p><strong>THE PLACE TO BE IN!</strong></p>
                        ',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        DB::table('email_templates')->where('type', 'a_new_review_added')->delete();
    }
};

?>