<?php
namespace App\Services;
use Illuminate\Support\Facades\Response;

class Resp extends Response
{
    public static function success($data = null, $message = 'Success')
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data]);
    }
}