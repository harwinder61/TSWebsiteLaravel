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
        Schema::table('verify', function (Blueprint $table) {
            // Add DIDiT related columns if they don't exist
            if (!Schema::hasColumn('verify', 'didit_session_id')) {
                $table->string('didit_session_id')->nullable()->after('verified_status')->index();
            }
            if (!Schema::hasColumn('verify', 'didit_session_token')) {
                $table->string('didit_session_token')->nullable()->after('didit_session_id');
            }
            if (!Schema::hasColumn('verify', 'didit_workflow_id')) {
                $table->string('didit_workflow_id')->nullable()->after('didit_session_token');
            }
            if (!Schema::hasColumn('verify', 'didit_status')) {
                $table->string('didit_status')->nullable()->after('didit_workflow_id')->comment('Approved, Declined, In Review');
            }
            if (!Schema::hasColumn('verify', 'didit_completed_at')) {
                $table->timestamp('didit_completed_at')->nullable()->after('didit_status');
            }
            if (!Schema::hasColumn('verify', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('didit_completed_at');
            }
            if (!Schema::hasColumn('verify', 'admin_reviewed_at')) {
                $table->timestamp('admin_reviewed_at')->nullable()->after('admin_notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('verify', function (Blueprint $table) {
            $table->dropIndexIfExists(['didit_session_id']);
            $table->dropColumn([
                'didit_session_id',
                'didit_session_token',
                'didit_workflow_id',
                'didit_status',
                'didit_completed_at',
                'admin_notes',
                'admin_reviewed_at',
            ]);
        });
    }
};
