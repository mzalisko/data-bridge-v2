<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteSocial;
use App\Services\ActivityService;
use App\Support\ApiResponse;
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
            return ApiResponse::forbidden('socials.write');
        }

        $validated = $request->validate([
            'platform'   => 'required|in:' . implode(',', self::PLATFORMS),
            'handle'     => 'nullable|string|max:100',
            'url'        => 'nullable|url|max:255',
            'sort_order' => 'integer',
        ]);

        $social = $site->socials()->create($validated);
        ActivityService::log('social', 'create', $social, "API: {$social->platform} {$social->handle} додано", $site, source: 'api');

        return ApiResponse::ok(['id' => $social->id, 'synced_at' => now()->toIso8601String()], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $site   = $request->attributes->get('site');
        $apiKey = $request->attributes->get('api_key');

        if (! $apiKey->hasPermission('socials.write')) {
            return ApiResponse::forbidden('socials.write');
        }

        $social = SiteSocial::where('id', $id)->where('site_id', $site->id)->first();

        if (! $social) {
            return ApiResponse::notFound('Social');
        }

        $validated = $request->validate([
            'platform'   => 'in:' . implode(',', self::PLATFORMS),
            'handle'     => 'nullable|string|max:100',
            'url'        => 'nullable|url|max:255',
            'sort_order' => 'integer',
        ]);

        $before = $social->toArray();
        $social->update($validated);
        ActivityService::log('social', 'update', $social, "API: {$social->platform} {$social->handle} оновлено", $site, $before, 'api');

        return ApiResponse::ok(['id' => $social->id, 'synced_at' => now()->toIso8601String()]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $site   = $request->attributes->get('site');
        $apiKey = $request->attributes->get('api_key');

        if (! $apiKey->hasPermission('socials.write')) {
            return ApiResponse::forbidden('socials.write');
        }

        $social = SiteSocial::where('id', $id)->where('site_id', $site->id)->first();

        if (! $social) {
            return ApiResponse::notFound('Social');
        }

        ActivityService::log('social', 'delete', $social, "API: {$social->platform} видалено", $site, source: 'api');
        $social->delete();

        return ApiResponse::ok(['deleted_id' => $id]);
    }
}
