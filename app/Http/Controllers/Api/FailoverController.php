<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

        // Scope to the site the API key resolves to — never trust body site_id
        // alone (any valid key could otherwise act on any other site).
        $site = $request->attributes->get('site');
        if (! $site || $site->id !== (int) $data['site_id']) {
            return response()->json(['ok' => false, 'error' => 'site_id does not match API key'], 403);
        }

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
     * POST /api/v1/failover/restore
     *
     * Restore original primary when external signal says it is active again.
     * Finds the latest active failover log for that primary and rolls it back.
     * The current active standby returns to the pool automatically.
     *
     * Body:
     *   site_id    int     required
     *   type       string  required  phone|social
     *   primary_id int     required  ID of the original primary record
     */
    public function restore(Request $request): JsonResponse
    {
        $data = $request->validate([
            'site_id'    => ['required', 'integer', 'exists:sites,id'],
            'type'       => ['required', Rule::in(['phone', 'social'])],
            'primary_id' => ['required', 'integer'],
        ]);

        // Scope to the site the API key resolves to — never trust body site_id
        // alone (any valid key could otherwise act on any other site).
        $site = $request->attributes->get('site');
        if (! $site || $site->id !== (int) $data['site_id']) {
            return response()->json(['ok' => false, 'error' => 'site_id does not match API key'], 403);
        }

        try {
            $log = FailoverService::restore($site, $data['type'], $data['primary_id']);
        } catch (\RuntimeException $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 422);
        }

        return response()->json([
            'ok'             => true,
            'primary_id'     => $data['primary_id'],
            'restored_at'    => $log->rolled_back_at->toISOString(),
        ]);
    }

    /**
     * POST /api/v1/failover/{log}/rollback
     *
     * Rollback a specific failover log by ID.
     * Use /restore when you know the primary_id but not the log ID.
     */
    public function rollback(Request $request, SiteFailoverLog $log): JsonResponse
    {
        // Scope to the site the API key resolves to — never trust the log ID
        // alone (any valid key could otherwise roll back any other site's log).
        $site = $request->attributes->get('site');
        if (! $site || $site->id !== (int) $log->site_id) {
            return response()->json(['ok' => false, 'error' => 'log does not belong to API key site'], 403);
        }

        try {
            $log = FailoverService::rollback($log);
        } catch (\RuntimeException $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 422);
        }

        return response()->json([
            'ok'             => true,
            'rolled_back_at' => $log->rolled_back_at->toISOString(),
        ]);
    }
}
