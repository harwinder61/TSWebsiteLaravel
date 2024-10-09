<?php
namespace App\Services;
use Illuminate\Support\Facades\Response;

class Resp extends Response
{
    public static function success(array $data = [], string $message = 'Success', $code = 200)
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data], $code);
    }
    public static function error(array $data = [], string $message = 'Error', $code = 400)
    {
        return response()->json(['success' => false, 'message' => $message, 'data' => $data], $code);
    }
}