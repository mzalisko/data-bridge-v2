<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SitePrice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiPriceController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $site   = $request->attributes->get('site');
        $apiKey = $request->attributes->get('api_key');

        if (! $apiKey->hasPermission('prices.write')) {
            return $this->forbidden('prices.write');
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

        return response()->json([
            'status'    => 'ok',
            'id'        => $price->id,
            'synced_at' => now()->toIso8601String(),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $site   = $request->attributes->get('site');
        $apiKey = $request->attributes->get('api_key');

        if (! $apiKey->hasPermission('prices.write')) {
            return $this->forbidden('prices.write');
        }

        $price = SitePrice::where('id', $id)->where('site_id', $site->id)->first();

        if (! $price) {
            return response()->json(['status' => 'error', 'code' => 404, 'message' => 'Price not found'], 404);
        }

        $validated = $request->validate([
            'label'      => 'nullable|string|max:100',
            'amount'     => 'numeric|min:0',
            'currency'   => 'in:UAH,USD,EUR',
            'period'     => 'nullable|string|max:50',
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $price->update($validated);

        return response()->json([
            'status'    => 'ok',
            'id'        => $price->id,
            'synced_at' => now()->toIso8601String(),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $site   = $request->attributes->get('site');
        $apiKey = $request->attributes->get('api_key');

        if (! $apiKey->hasPermission('prices.write')) {
            return $this->forbidden('prices.write');
        }

        $price = SitePrice::where('id', $id)->where('site_id', $site->id)->first();

        if (! $price) {
            return response()->json(['status' => 'error', 'code' => 404, 'message' => 'Price not found'], 404);
        }

        $price->delete();

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
