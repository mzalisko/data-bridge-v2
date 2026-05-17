<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Services\ActivityService;
use App\Services\SyncPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PluginCallbackController extends Controller
{
    private const ALLOWED = [
        'phones'        => ['number', 'label', 'is_visible'],
        'prices'        => ['label', 'amount', 'is_visible'],
        'addresses'     => ['city', 'street', 'is_visible'],
        'socials'       => ['handle', 'url', 'is_visible'],
        'custom_fields' => ['field_value'],
    ];

    private const MODELS = [
        'phones'        => \App\Models\SitePhone::class,
        'prices'        => \App\Models\SitePrice::class,
        'addresses'     => \App\Models\SiteAddress::class,
        'socials'       => \App\Models\SiteSocial::class,
        'custom_fields' => \App\Models\SiteCustomField::class,
    ];

    private const ENTITY_TYPES = [
        'phones'        => 'phone',
        'prices'        => 'price',
        'addresses'     => 'address',
        'socials'       => 'social',
        'custom_fields' => 'field',
    ];

    public function handle(Request $request, string $token): JsonResponse
    {
        $site = Site::where('plugin_edit_token', $token)
            ->where('allow_plugin_edit', true)
            ->first();

        if (!$site) {
            return response()->json(['ok' => false, 'error' => 'not_found'], 404);
        }

        $body = $request->getContent();
        $expected = 'sha256=' . hash_hmac('sha256', $body, $site->push_key);
        if (!hash_equals($expected, $request->header('X-DBP-Signature', ''))) {
            return response()->json(['ok' => false, 'error' => 'invalid_signature'], 403);
        }

        $data = json_decode($body, true) ?? [];
        if (abs(now()->timestamp - ($data['timestamp'] ?? 0)) > 300) {
            return response()->json(['ok' => false, 'error' => 'timestamp_expired'], 403);
        }

        $type  = $data['type'] ?? '';
        $crmId = (int) ($data['crm_id'] ?? 0);

        if (!isset(self::ALLOWED[$type]) || !$crmId) {
            return response()->json(['ok' => false, 'error' => 'invalid_request'], 422);
        }

        $record = self::MODELS[$type]::where('site_id', $site->id)
            ->where('id', $crmId)
            ->first();

        if (!$record) {
            return response()->json(['ok' => false, 'error' => 'record_not_found'], 404);
        }

        $update = array_intersect_key(
            $data['data'] ?? [],
            array_flip(self::ALLOWED[$type])
        );

        if (!empty($update)) {
            $before = $record->toArray();
            $record->update($update);
            $entityType = self::ENTITY_TYPES[$type];
            ActivityService::log($entityType, 'update', $record, "Plugin: {$entityType} оновлено", $site, $before, 'plugin');
        }

        SyncPushService::push($site);

        return response()->json(['ok' => true, 'synced_at' => now()->toISOString()]);
    }
}
