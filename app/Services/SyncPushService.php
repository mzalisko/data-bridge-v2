<?php

namespace App\Services;

use App\Models\Site;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncPushService
{
    public static function push(Site $site): bool
    {
        if (!$site->push_url || !$site->push_key) return false;

        $site->loadMissing(['phones', 'prices', 'addresses', 'socials', 'customFields']);

        $body = [
            'timestamp'     => now()->timestamp,
            'phones'        => $site->phones->toArray(),
            'prices'        => $site->prices->toArray(),
            'addresses'     => $site->addresses->toArray(),
            'socials'       => $site->socials->toArray(),
            'custom_fields' => $site->customFields->toArray(),
        ];

        if ($site->allow_plugin_edit && $site->plugin_edit_token) {
            $body['edit_callback'] = [
                'url' => config('app.url') . '/api/plugin-callback/' . $site->plugin_edit_token,
                'key' => $site->push_key,
            ];
        }

        $payload = json_encode($body);

        $sig = 'sha256=' . hash_hmac('sha256', $payload, $site->push_key);

        try {
            $response = Http::withHeaders([
                'Content-Type'    => 'application/json',
                'X-DBP-Signature' => $sig,
                'X-DBP-Timestamp' => (string) now()->timestamp,
            ])->timeout(6)->send('POST', $site->push_url, ['body' => $payload]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning('SyncPushService failed', ['site' => $site->id, 'error' => $e->getMessage()]);
            return false;
        }
    }
}
