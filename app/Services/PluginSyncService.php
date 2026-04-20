<?php

namespace App\Services;

use App\Models\Site;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PluginSyncService
{
    /**
     * Fire-and-forget HTTP ping to the site's plugin webhook URL.
     * Triggers an immediate sync on the WordPress plugin side.
     * Silently fails if URL not configured or request fails.
     */
    public static function ping(Site $site): void
    {
        $url = $site->plugin_webhook_url ?? '';
        if (empty($url)) return;

        try {
            Http::timeout(3)
                ->async()
                ->post($url)
                ->wait();
        } catch (\Throwable $e) {
            Log::debug('PluginSyncService ping failed', [
                'site_id' => $site->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
