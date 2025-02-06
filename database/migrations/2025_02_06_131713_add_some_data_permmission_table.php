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
        // Insert data for existing IDs
        $permissions = DB::table('permissions')->pluck('id'); // Get existing IDs
        $values = ['P101', 'P102', 'P103', 'P104', 'P105', 'P106', 'P107', 'P108', 'P109', 'P110', 'P111', 'P112']; // Add all values as needed

        foreach ($permissions as $index => $id) {
            if (isset($values[$index])) {
                DB::table('permissions')->where('id', $id)->update([
                    'code' => $values[$index] // Set the value for the new column
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // If you need to revert the changes, you can drop the column here
        // Schema::table('permissions', function (Blueprint $table) {
        //     $table->dropColumn('new_column_name'); // Uncomment if needed
        // });
    }
};
