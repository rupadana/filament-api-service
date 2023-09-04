<?php

namespace Rupadana\ApiService\Traits;
trait HttpResponse {
    public static function sendSuccessResponse($data, $message = "ok") {
        return response()->json([
            'message' => $message,
            'data' => $data
        ]);
    }

    public static function sendNotFoundResponse($message='resource not found') {
        return response()->json([
            'message' => $message
        ], 404);
    }
}