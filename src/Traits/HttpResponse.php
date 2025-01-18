<?php

namespace Rupadana\ApiService\Traits;

trait HttpResponse
{
    public static function sendSuccessResponse($data, $message = 'ok'): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ]);
    }

    public static function sendNotFoundResponse($message = 'resource not found'): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], 404);
    }
}
