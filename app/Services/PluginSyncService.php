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
        $siteUrl = $site->url ?? '';
        if (empty($siteUrl)) return;

        $url = rtrim($siteUrl, '/') . '/wp-admin/admin-ajax.php?action=databridge_sync_trigger';

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
