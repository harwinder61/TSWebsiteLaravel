<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Get all permissions (id and current code)
        $permissions = DB::table('permissions')->pluck('id', 'code'); 

        // Prepare the mapping from 'P101', 'P102', ..., to 'PRM101', 'PRM102', ...
        $values = [
            'P101' => 'PRM101', 'P102' => 'PRM102', 'P103' => 'PRM103', 
            'P104' => 'PRM104', 'P105' => 'PRM105', 'P106' => 'PRM106', 
            'P107' => 'PRM107', 'P108' => 'PRM108', 'P109' => 'PRM109', 
            'P110' => 'PRM110', 'P111' => 'PRM111', 'P112' => 'PRM112'
        ];

        foreach ($permissions as $code => $id) {
            // Check if the permission code exists in the $values array
            if (array_key_exists($code, $values)) {
                // Update the permission code to the corresponding PRM code
                DB::table('permissions')->where('id', $id)->update([
                    'code' => $values[$code] 
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert all changes by switching from 'PRM101' to 'P101', and so on
        $permissions = DB::table('permissions')->pluck('id', 'code'); 

        // Prepare the reverse mapping from 'PRM101', 'PRM102', ..., to 'P101', 'P102', ...
        $values = [
            'PRM101' => 'P101', 'PRM102' => 'P102', 'PRM103' => 'P103', 
            'PRM104' => 'P104', 'PRM105' => 'P105', 'PRM106' => 'P106', 
            'PRM107' => 'P107', 'PRM108' => 'P108', 'PRM109' => 'P109', 
            'PRM110' => 'P110', 'PRM111' => 'P111', 'PRM112' => 'P112'
        ];

        foreach ($permissions as $code => $id) {
            // Check if the permission code exists in the $values array
            if (array_key_exists($code, $values)) {
                // Revert the permission code to the corresponding P code
                DB::table('permissions')->where('id', $id)->update([
                    'code' => $values[$code] 
                ]);
            }
        }
    }
};
