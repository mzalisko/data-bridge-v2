<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SitePhone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiPhoneController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $site   = $request->attributes->get('site');
        $apiKey = $request->attributes->get('api_key');

        if (! $apiKey->hasPermission('phones.write')) {
            return $this->forbidden('phones.write');
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

        return response()->json([
            'status'    => 'ok',
            'id'        => $phone->id,
            'synced_at' => now()->toIso8601String(),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $site   = $request->attributes->get('site');
        $apiKey = $request->attributes->get('api_key');

        if (! $apiKey->hasPermission('phones.write')) {
            return $this->forbidden('phones.write');
        }

        $phone = SitePhone::where('id', $id)->where('site_id', $site->id)->first();

        if (! $phone) {
            return response()->json(['status' => 'error', 'code' => 404, 'message' => 'Phone not found'], 404);
        }

        $validated = $request->validate([
            'label'      => 'nullable|string|max:100',
            'country_iso' => 'string|max:3',
            'dial_code'  => 'string|max:8',
            'number'     => 'string|max:32',
            'is_primary' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $phone->update($validated);

        return response()->json([
            'status'    => 'ok',
            'id'        => $phone->id,
            'synced_at' => now()->toIso8601String(),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $site   = $request->attributes->get('site');
        $apiKey = $request->attributes->get('api_key');

        if (! $apiKey->hasPermission('phones.write')) {
            return $this->forbidden('phones.write');
        }

        $phone = SitePhone::where('id', $id)->where('site_id', $site->id)->first();

        if (! $phone) {
            return response()->json(['status' => 'error', 'code' => 404, 'message' => 'Phone not found'], 404);
        }

        $phone->delete();

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
