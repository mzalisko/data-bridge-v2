<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteSocial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiSocialController extends Controller
{
    private const PLATFORMS = [
        'instagram', 'facebook', 'telegram', 'tiktok',
        'youtube', 'twitter', 'linkedin', 'viber', 'whatsapp', 'other',
    ];

    public function store(Request $request): JsonResponse
    {
        $site   = $request->attributes->get('site');
        $apiKey = $request->attributes->get('api_key');

        if (! $apiKey->hasPermission('socials.write')) {
            return $this->forbidden('socials.write');
        }

        $validated = $request->validate([
            'platform'   => 'required|in:' . implode(',', self::PLATFORMS),
            'handle'     => 'nullable|string|max:100',
            'url'        => 'nullable|url|max:255',
            'sort_order' => 'integer',
        ]);

        $social = $site->socials()->create($validated);

        return response()->json([
            'status'    => 'ok',
            'id'        => $social->id,
            'synced_at' => now()->toIso8601String(),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $site   = $request->attributes->get('site');
        $apiKey = $request->attributes->get('api_key');

        if (! $apiKey->hasPermission('socials.write')) {
            return $this->forbidden('socials.write');
        }

        $social = SiteSocial::where('id', $id)->where('site_id', $site->id)->first();

        if (! $social) {
            return response()->json(['status' => 'error', 'code' => 404, 'message' => 'Social not found'], 404);
        }

        $validated = $request->validate([
            'platform'   => 'in:' . implode(',', self::PLATFORMS),
            'handle'     => 'nullable|string|max:100',
            'url'        => 'nullable|url|max:255',
            'sort_order' => 'integer',
        ]);

        $social->update($validated);

        return response()->json([
            'status'    => 'ok',
            'id'        => $social->id,
            'synced_at' => now()->toIso8601String(),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $site   = $request->attributes->get('site');
        $apiKey = $request->attributes->get('api_key');

        if (! $apiKey->hasPermission('socials.write')) {
            return $this->forbidden('socials.write');
        }

        $social = SiteSocial::where('id', $id)->where('site_id', $site->id)->first();

        if (! $social) {
            return response()->json(['status' => 'error', 'code' => 404, 'message' => 'Social not found'], 404);
        }

        $social->delete();

        return response()->json(['status' => 'ok', 'deleted_id' => $id]);
    }

    private function forbidden(string $permission): JsonResponse
    {
        return response()->json([
            'status'  => 'error',
            'code'    => 403,
            'message' => "Permission denied: {$permission}",
        ], 403);
    }
}
