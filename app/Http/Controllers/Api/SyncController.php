<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\SyncLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    public function health(): JsonResponse
    {
        return response()->json([
            'status'    => 'ok',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * GET /api/v1/sync — full pull for all data types.
     * Optional ?since=UNIX_TIMESTAMP for delta sync.
     */
    public function pull(Request $request): JsonResponse
    {
        $site    = $request->attributes->get('site');
        $apiKey  = $request->attributes->get('api_key');
        $start   = microtime(true);

        $since   = $request->query('since');
        $sinceDate = $since ? date('Y-m-d H:i:s', (int) $since) : null;

        $data = [
            'phones'    => $this->fetchPhones($site, $sinceDate),
            'prices'    => $this->fetchPrices($site, $sinceDate),
            'addresses' => $this->fetchAddresses($site, $sinceDate),
            'socials'   => $this->fetchSocials($site, $sinceDate),
        ];

        $checksum = 'sha256:' . hash('sha256', json_encode($data));

        $durationMs = (int) round((microtime(true) - $start) * 1000);

        SyncLog::create([
            'site_id'     => $site->id,
            'status'      => 'ok',
            'duration_ms' => $durationMs,
            'checksum'    => $checksum,
        ]);

        return response()->json([
            'status'    => 'ok',
            'site_id'   => $site->id,
            'synced_at' => now()->toIso8601String(),
            'data'      => $data,
            'checksum'  => $checksum,
        ]);
    }

    public function pullPhones(Request $request): JsonResponse
    {
        return $this->pullSingle($request, 'phones');
    }

    public function pullPrices(Request $request): JsonResponse
    {
        return $this->pullSingle($request, 'prices');
    }

    public function pullAddresses(Request $request): JsonResponse
    {
        return $this->pullSingle($request, 'addresses');
    }

    public function pullSocials(Request $request): JsonResponse
    {
        return $this->pullSingle($request, 'socials');
    }

    private function pullSingle(Request $request, string $type): JsonResponse
    {
        $site     = $request->attributes->get('site');
        $apiKey   = $request->attributes->get('api_key');
        $since    = $request->query('since');
        $sinceDate = $since ? date('Y-m-d H:i:s', (int) $since) : null;

        $permission = rtrim($type, 's') . 's.read';
        // Normalise: phones.read, prices.read, addresses.read, socials.read
        $permission = $type . '.read';

        if (! $apiKey->hasPermission($permission)) {
            return response()->json([
                'status'  => 'error',
                'code'    => 403,
                'message' => "Permission denied: {$permission}",
            ], 403);
        }

        $data = match ($type) {
            'phones'    => $this->fetchPhones($site, $sinceDate),
            'prices'    => $this->fetchPrices($site, $sinceDate),
            'addresses' => $this->fetchAddresses($site, $sinceDate),
            'socials'   => $this->fetchSocials($site, $sinceDate),
        };

        $checksum = 'sha256:' . hash('sha256', json_encode($data));

        return response()->json([
            'status'    => 'ok',
            'site_id'   => $site->id,
            'synced_at' => now()->toIso8601String(),
            'data'      => $data,
            'checksum'  => $checksum,
        ]);
    }

    private function fetchPhones(Site $site, ?string $since): array
    {
        $q = $site->phones();
        if ($since) {
            $q->where('updated_at', '>', $since);
        }
        return $q->orderBy('sort_order')->get()->toArray();
    }

    private function fetchPrices(Site $site, ?string $since): array
    {
        $q = $site->prices();
        if ($since) {
            $q->where('updated_at', '>', $since);
        }
        return $q->orderBy('sort_order')->get()->toArray();
    }

    private function fetchAddresses(Site $site, ?string $since): array
    {
        $q = $site->addresses();
        if ($since) {
            $q->where('updated_at', '>', $since);
        }
        return $q->orderBy('sort_order')->get()->toArray();
    }

    private function fetchSocials(Site $site, ?string $since): array
    {
        $q = $site->socials();
        if ($since) {
            $q->where('updated_at', '>', $since);
        }
        return $q->orderBy('sort_order')->get()->toArray();
    }
}
