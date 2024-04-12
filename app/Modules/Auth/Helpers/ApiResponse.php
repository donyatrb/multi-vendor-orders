<?php

namespace App\Modules\Auth\Helpers;

class ApiResponse
{
    public static function success($data = null, $message = null)
    {
        return response()->json([
            'status' => true,
            'data' => $data,
            'message' => $message,
        ]);
    }

    public static function error($message, $statusCode = 500)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
        ], $statusCode);
    }
}
