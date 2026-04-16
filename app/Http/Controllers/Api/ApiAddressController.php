<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiAddressController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $site   = $request->attributes->get('site');
        $apiKey = $request->attributes->get('api_key');

        if (! $apiKey->hasPermission('addresses.write')) {
            return $this->forbidden('addresses.write');
        }

        $validated = $request->validate([
            'label'       => 'nullable|string|max:100',
            'country_iso' => 'required|string|max:3',
            'city'        => 'required|string|max:100',
            'street'      => 'nullable|string|max:255',
            'building'    => 'nullable|string|max:32',
            'postal_code' => 'nullable|string|max:16',
            'latitude'    => 'nullable|numeric|between:-90,90',
            'longitude'   => 'nullable|numeric|between:-180,180',
            'is_primary'  => 'boolean',
            'sort_order'  => 'integer',
        ]);

        $address = $site->addresses()->create($validated);

        return response()->json([
            'status'    => 'ok',
            'id'        => $address->id,
            'synced_at' => now()->toIso8601String(),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $site   = $request->attributes->get('site');
        $apiKey = $request->attributes->get('api_key');

        if (! $apiKey->hasPermission('addresses.write')) {
            return $this->forbidden('addresses.write');
        }

        $address = SiteAddress::where('id', $id)->where('site_id', $site->id)->first();

        if (! $address) {
            return response()->json(['status' => 'error', 'code' => 404, 'message' => 'Address not found'], 404);
        }

        $validated = $request->validate([
            'label'       => 'nullable|string|max:100',
            'country_iso' => 'string|max:3',
            'city'        => 'string|max:100',
            'street'      => 'nullable|string|max:255',
            'building'    => 'nullable|string|max:32',
            'postal_code' => 'nullable|string|max:16',
            'latitude'    => 'nullable|numeric|between:-90,90',
            'longitude'   => 'nullable|numeric|between:-180,180',
            'is_primary'  => 'boolean',
            'sort_order'  => 'integer',
        ]);

        $address->update($validated);

        return response()->json([
            'status'    => 'ok',
            'id'        => $address->id,
            'synced_at' => now()->toIso8601String(),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $site   = $request->attributes->get('site');
        $apiKey = $request->attributes->get('api_key');

        if (! $apiKey->hasPermission('addresses.write')) {
            return $this->forbidden('addresses.write');
        }

        $address = SiteAddress::where('id', $id)->where('site_id', $site->id)->first();

        if (! $address) {
            return response()->json(['status' => 'error', 'code' => 404, 'message' => 'Address not found'], 404);
        }

        $address->delete();

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
