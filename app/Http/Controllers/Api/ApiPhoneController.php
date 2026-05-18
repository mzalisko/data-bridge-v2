<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SitePhone;
use App\Services\ActivityService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiPhoneController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $site   = $request->attributes->get('site');
        $apiKey = $request->attributes->get('api_key');

        if (! $apiKey->hasPermission('phones.write')) {
            return ApiResponse::forbidden('phones.write');
        }

        $validated = $request->validate([
            'label'      => 'nullable|string|max:100',
            'country_iso' => 'required|string|max:3',
            'dial_code'  => 'required|string|max:8',
            'number'     => 'required|string|max:32',
            'is_primary' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $phone = $site->phones()->create($validated);
        ActivityService::log('phone', 'create', $phone, "API: телефон {$phone->number} додано", $site, source: 'api');

        return ApiResponse::ok(['id' => $phone->id, 'synced_at' => now()->toIso8601String()], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $site   = $request->attributes->get('site');
        $apiKey = $request->attributes->get('api_key');

        if (! $apiKey->hasPermission('phones.write')) {
            return ApiResponse::forbidden('phones.write');
        }

        $phone = SitePhone::where('id', $id)->where('site_id', $site->id)->first();

        if (! $phone) {
            return ApiResponse::notFound('Phone');
        }

        $validated = $request->validate([
            'label'      => 'nullable|string|max:100',
            'country_iso' => 'string|max:3',
            'dial_code'  => 'string|max:8',
            'number'     => 'string|max:32',
            'is_primary' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $before = $phone->toArray();
        $phone->update($validated);
        ActivityService::log('phone', 'update', $phone, "API: телефон {$phone->number} оновлено", $site, $before, 'api');

        return ApiResponse::ok(['id' => $phone->id, 'synced_at' => now()->toIso8601String()]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $site   = $request->attributes->get('site');
        $apiKey = $request->attributes->get('api_key');

        if (! $apiKey->hasPermission('phones.write')) {
            return ApiResponse::forbidden('phones.write');
        }

        $phone = SitePhone::where('id', $id)->where('site_id', $site->id)->first();

        if (! $phone) {
            return ApiResponse::notFound('Phone');
        }

        ActivityService::log('phone', 'delete', $phone, "API: телефон {$phone->number} видалено", $site, source: 'api');
        $phone->delete();

        return ApiResponse::ok(['deleted_id' => $id]);
    }
}
