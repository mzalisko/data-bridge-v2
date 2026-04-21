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
        // Use explicit sync URL if set (e.g. Docker internal: http://wp1),
        // otherwise auto-construct from public site URL.
        $base = $site->plugin_webhook_url ?: ($site->url ?? '');
        if (empty($base)) return;

        $url = rtrim($base, '/') . '/wp-admin/admin-ajax.php?action=databridge_sync_trigger';

        try {
            Http::timeout(3)->post($url);
        } catch (\Throwable $e) {
            Log::debug('PluginSyncService ping failed', [
                'site_id' => $site->id,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
