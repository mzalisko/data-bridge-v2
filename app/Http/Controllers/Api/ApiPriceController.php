<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SitePrice;
use App\Services\ActivityService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiPriceController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $site   = $request->attributes->get('site');
        $apiKey = $request->attributes->get('api_key');

        if (! $apiKey->hasPermission('prices.write')) {
            return ApiResponse::forbidden('prices.write');
        }

        $validated = $request->validate([
            'label'      => 'nullable|string|max:100',
            'amount'     => 'required|numeric|min:0',
            'currency'   => 'required|in:UAH,USD,EUR',
            'period'     => 'nullable|string|max:50',
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $price = $site->prices()->create($validated);
        ActivityService::log('price', 'create', $price, "API: ціна «{$price->label}» додана", $site, source: 'api');

        return ApiResponse::ok(['id' => $price->id, 'synced_at' => now()->toIso8601String()], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $site   = $request->attributes->get('site');
        $apiKey = $request->attributes->get('api_key');

        if (! $apiKey->hasPermission('prices.write')) {
            return ApiResponse::forbidden('prices.write');
        }

        $price = SitePrice::where('id', $id)->where('site_id', $site->id)->first();

        if (! $price) {
            return ApiResponse::notFound('Price');
        }

        $validated = $request->validate([
            'label'      => 'nullable|string|max:100',
            'amount'     => 'numeric|min:0',
            'currency'   => 'in:UAH,USD,EUR',
            'period'     => 'nullable|string|max:50',
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $before = $price->toArray();
        $price->update($validated);
        ActivityService::log('price', 'update', $price, "API: ціна «{$price->label}» оновлена", $site, $before, 'api');

        return ApiResponse::ok(['id' => $price->id, 'synced_at' => now()->toIso8601String()]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $site   = $request->attributes->get('site');
        $apiKey = $request->attributes->get('api_key');

        if (! $apiKey->hasPermission('prices.write')) {
            return ApiResponse::forbidden('prices.write');
        }

        $price = SitePrice::where('id', $id)->where('site_id', $site->id)->first();

        if (! $price) {
            return ApiResponse::notFound('Price');
        }

        ActivityService::log('price', 'delete', $price, "API: ціна «{$price->label}» видалена", $site, source: 'api');
        $price->delete();

        return ApiResponse::ok(['deleted_id' => $id]);
    }
}
