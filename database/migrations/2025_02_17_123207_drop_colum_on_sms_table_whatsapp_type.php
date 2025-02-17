<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Admin\app\Models\SmsTemplates;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Delete records with the specified type
        SmsTemplates::where('type', 'whatapp_admin_new_user_added')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally, you could add back the records if you have a way to do so,
        // but typically, deletions are not reversible unless you have a backup.
    }
};
