<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class ApiResponse
{
    public static function success($data = null, $message = 'Success', $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    public static function error($message = 'Error', $errors = null, $status = 400)
    {
        if ($status == 500 && app()->environment('production')) {
            if (!empty($errors)) {
                Log::error('[API ERROR] ' . $message, [
                    'errors' => $errors
                ]);
            }

            $errors = null;
        }

        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
            'data'    => null,
        ], $status);
    }
}