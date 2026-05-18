<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

/**
 * Standard JSON envelopes for the /api/v1 surface.
 * Replaces the per-controller copy-pasted forbidden()/404/ok blocks
 * (audit S8). Shapes are kept byte-identical to the pre-refactor
 * responses so existing plugin clients are unaffected.
 */
class ApiResponse
{
    public static function ok(array $payload = [], int $status = 200): JsonResponse
    {
        return response()->json(['status' => 'ok'] + $payload, $status);
    }

    public static function error(string $message, int $code): JsonResponse
    {
        return response()->json([
            'status'  => 'error',
            'code'    => $code,
            'message' => $message,
        ], $code);
    }

    public static function forbidden(string $permission): JsonResponse
    {
        return self::error("Permission denied: {$permission}", 403);
    }

    public static function notFound(string $resource): JsonResponse
    {
        return self::error("{$resource} not found", 404);
    }
}
