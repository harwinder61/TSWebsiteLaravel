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
            'subject' => 'Admin Welcome Email',
            'type' => 'ts_admin_welcome_email',
            'title' => 'Admin Welcome Email',
            'content' => '<p>Here is your new Admin profile at Transbunnies</p>
                        <p>Below is your login details</p>
                        <p>Url : [LOGIN_URL]</p>
                        <p>Username: [USER_LOGIN]</p>
                        <p>Password: [USER_PASSWORD]</p>
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
        DB::table('email_templates')->where('type', 'ts_admin_welcome_email')->delete();
    }
};

?>