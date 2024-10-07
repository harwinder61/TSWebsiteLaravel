<?php
use Illuminate\Support\Facades\Log;
if (!function_exists('test')) {
    function test()
    {
        Log::info("test helper function here-----------------------------------");

    }
}
?>