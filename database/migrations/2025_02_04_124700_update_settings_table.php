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
        $updatedData = [
            [
                'key' => 'ADS_PAGE_IMG',
                'value' => json_encode([
                    'images' => ['European', 'African', 'Asian'],
                    'url' => 'https://yourdomain.com/ads' // Add your URL here
                ]),
            ],
            [
                'key' => 'ACCOUNT_PAGE_IMG',
                'value' => json_encode([
                    'images' => ['European', 'African', 'Asian'],
                    'url' => 'https://yourdomain.com/account' // Add your URL here
                ]),
            ],
            [
                'key' => 'mobile_parallax',
                'value' => json_encode([
                    'images' => [1800],
                    'url' => 'https://yourdomain.com/about' // Add your URL here
                ]),
            ],
            [
                'key' => 'desktop_parallax',
                'value' => json_encode([
                    'images' => [1799],
                    'url' => 'https://yourdomain.com/about' // Add your URL here
                ]),
            ],
            [
                'key' => 'HOME_AD_IMAGE',
                'value' => json_encode([
                    'images' => [1869],
                    'url' => 'https://yourdomain.com/about' // Add your URL here
                ]),
            ],
        ];

        foreach ($updatedData as $data) {
            DB::table('settings')
                ->where('key', $data['key'])
                ->update(['value' => $data['value']]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally, you can revert the changes if needed
        DB::table('settings')->where('key', 'ADS_PAGE_IMG')->update(['value' => json_encode(['European', 'African', 'Asian'])]);
        DB::table('settings')->where('key', 'HOME_AD_IMAGE')->update(['value' => json_encode(['European', 'African', 'Asian'])]);
        DB::table('settings')->where('key', 'mobile_parallax')->update(['value' => json_encode(['European', 'African', 'Asian'])]);
        DB::table('settings')->where('key', 'desktop_parallax')->update(['value' => json_encode(['European', 'African', 'Asian'])]);
        DB::table('settings')->where('key', 'ACCOUNT_PAGE_IMG')->update(['value' => json_encode(['European', 'African', 'Asian'])]);
    }
};
