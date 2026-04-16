<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteCustomField;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiCustomFieldController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $site   = $request->attributes->get('site');
        $apiKey = $request->attributes->get('api_key');

        if (! $apiKey->hasPermission('custom_fields.write')) {
            return $this->forbidden('custom_fields.write');
        }

        $validated = $request->validate([
            'field_key'   => 'required|string|max:128|regex:/^[a-z][a-z0-9_]{0,127}$/',
            'field_value' => 'required|string',
            'field_type'  => 'nullable|string|in:text,number,url,email,json',
            'sort_order'  => 'integer',
        ]);

        $field = $site->customFields()->create($validated);

        return response()->json([
            'status'    => 'ok',
            'id'        => $field->id,
            'synced_at' => now()->toIso8601String(),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $site   = $request->attributes->get('site');
        $apiKey = $request->attributes->get('api_key');

        if (! $apiKey->hasPermission('custom_fields.write')) {
            return $this->forbidden('custom_fields.write');
        }

        $field = SiteCustomField::where('id', $id)->where('site_id', $site->id)->first();

        if (! $field) {
            return response()->json(['status' => 'error', 'code' => 404, 'message' => 'Custom field not found'], 404);
        }

        $validated = $request->validate([
            'field_key'   => 'string|max:128|regex:/^[a-z][a-z0-9_]{0,127}$/',
            'field_value' => 'string',
            'field_type'  => 'nullable|string|in:text,number,url,email,json',
            'sort_order'  => 'integer',
        ]);

        $field->update($validated);

        return response()->json([
            'status'    => 'ok',
            'id'        => $field->id,
            'synced_at' => now()->toIso8601String(),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $site   = $request->attributes->get('site');
        $apiKey = $request->attributes->get('api_key');

        if (! $apiKey->hasPermission('custom_fields.write')) {
            return $this->forbidden('custom_fields.write');
        }

        $field = SiteCustomField::where('id', $id)->where('site_id', $site->id)->first();

        if (! $field) {
            return response()->json(['status' => 'error', 'code' => 404, 'message' => 'Custom field not found'], 404);
        }

        $field->delete();

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
