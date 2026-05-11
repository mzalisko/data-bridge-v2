<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\SiteFailoverLog;
use App\Services\FailoverService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FailoverController extends Controller
{
    /**
     * POST /api/v1/failover
     *
     * Trigger failover for a blocked phone or messenger.
     * Called by external API (e.g. WhatsApp gateway) when a number/account is banned.
     *
     * Body:
     *   site_id          int       required
     *   type             string    required  phone|social
     *   primary_id       int       required  ID of blocked record
     *   reason           string    required  Human-readable cause
     *   trigger_identifier string  optional  External signal/event ID
     */
    public function trigger(Request $request): JsonResponse
    {
        $data = $request->validate([
            'site_id'            => ['required', 'integer', 'exists:sites,id'],
            'type'               => ['required', Rule::in(['phone', 'social'])],
            'primary_id'         => ['required', 'integer'],
            'reason'             => ['required', 'string', 'max:255'],
            'trigger_identifier' => ['nullable', 'string', 'max:255'],
        ]);

        $site = Site::findOrFail($data['site_id']);

        try {
            $log = FailoverService::trigger(
                site:       $site,
                type:       $data['type'],
                primaryId:  $data['primary_id'],
                reason:     $data['reason'],
                triggeredBy: 'api',
                identifier:  $data['trigger_identifier'] ?? null,
            );
        } catch (\RuntimeException $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 422);
        }

        return response()->json([
            'ok'         => true,
            'failover_id' => $log->id,
            'standby_id'  => $log->standby_id,
            'triggered_at' => $log->created_at->toISOString(),
        ]);
    }

    /**
     * POST /api/v1/failover/{log}/rollback
     *
     * Rollback a failover — restore primary, demote standby back to pool.
     * Can be called by external API or triggered manually from CRM.
     */
    public function rollback(SiteFailoverLog $log): JsonResponse
    {
        try {
            $log = FailoverService::rollback($log);
        } catch (\RuntimeException $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 422);
        }

        return response()->json([
            'ok'              => true,
            'rolled_back_at'  => $log->rolled_back_at->toISOString(),
        ]);
    }
}
