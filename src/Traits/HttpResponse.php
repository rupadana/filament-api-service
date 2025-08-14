<?php

namespace Rupadana\ApiService\Traits;

use Illuminate\Http\JsonResponse;

trait HttpResponse
{
    public static function sendSuccessResponse($data, $message = 'ok'): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ]);
    }

    public static function sendNotFoundResponse($message = 'resource not found'): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], 404);
    }
}
