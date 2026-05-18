<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $bearerToken = $request->bearerToken();

        if (! $bearerToken) {
            return response()->json([
                'status'  => 'error',
                'code'    => 401,
                'message' => 'API key required',
            ], 401);
        }

        // Find candidate keys by prefix (first 12 chars). The prefix is NOT
        // unique — collisions are realistic at scale — so verify() every
        // candidate instead of trusting first() (DB-C1: prevents
        // nondeterministic auth failure when two keys share a prefix).
        $prefix = substr($bearerToken, 0, 12);
        $apiKey = ApiKey::where('key_prefix', $prefix)
            ->whereNull('revoked_at')
            ->get()
            ->first(fn (ApiKey $k) => $k->verify($bearerToken));

        if (! $apiKey) {
            return response()->json([
                'status'  => 'error',
                'code'    => 401,
                'message' => 'Invalid API key',
            ], 401);
        }

        $site = $apiKey->site;

        if (! $site || ! $site->is_active) {
            return response()->json([
                'status'  => 'error',
                'code'    => 403,
                'message' => 'Site is inactive',
            ], 403);
        }

        // Update last_used timestamp
        $apiKey->update(['last_used' => now()]);

        // Attach resolved objects to request for downstream controllers
        $request->attributes->set('api_key', $apiKey);
        $request->attributes->set('site', $site);

        return $next($request);
    }
}
