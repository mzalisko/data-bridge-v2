<?php

namespace App\Services;

use App\Models\Site;
use App\Models\SitePhone;
use App\Models\SiteSocial;
use App\Models\SiteFailoverLog;
use Illuminate\Support\Facades\DB;

class FailoverService
{
    // Platforms that support failover (messengers only — not social networks)
    public const MESSENGER_PLATFORMS = ['whatsapp', 'telegram', 'viber', 'signal', 'line', 'wechat'];

    /**
     * Trigger failover: mark primary as blocked, activate standby.
     *
     * @param  int|null $standbyId  Explicit standby to use; null = prefer paired, then first available
     * @throws \RuntimeException
     */
    public static function trigger(
        Site $site,
        string $type,
        int $primaryId,
        string $reason,
        string $triggeredBy = 'api',
        ?string $identifier = null,
        ?int $standbyId = null,
    ): SiteFailoverLog {
        [$model] = self::resolve($type);

        $primary = $model::where('site_id', $site->id)->findOrFail($primaryId);

        if ($primary->is_blocked) {
            throw new \RuntimeException("Record #{$primaryId} is already blocked.");
        }

        // Resolve standby: explicit → paired (standby_for_id) → first general pool
        $standby = null;
        if ($standbyId) {
            $standby = $model::where('site_id', $site->id)
                ->where('is_standby', true)
                ->where('is_blocked', false)
                ->find($standbyId);
        }

        if (!$standby) {
            // Try to find a standby paired specifically to this primary, lowest sort_order first
            $standby = $model::where('site_id', $site->id)
                ->where('is_standby', true)
                ->where('is_blocked', false)
                ->where('is_visible', true)
                ->where('standby_for_id', $primaryId)
                ->orderBy('sort_order')
                ->first();
        }

        if (!$standby) {
            // Fall back to general pool (unlinked standbys), lowest sort_order first
            $standby = $model::where('site_id', $site->id)
                ->where('is_standby', true)
                ->where('is_blocked', false)
                ->where('is_visible', true)
                ->whereNull('standby_for_id')
                ->orderBy('sort_order')
                ->first();
        }

        if (!$standby) {
            throw new \RuntimeException("No available standby {$type} for site #{$site->id}.");
        }

        return DB::transaction(function () use ($site, $type, $primary, $standby, $reason, $triggeredBy, $identifier) {
            $snapshot = [
                'primary_before' => $primary->toArray(),
                'standby_before' => $standby->toArray(),
            ];

            $primary->update([
                'is_blocked'     => true,
                'is_visible'     => false,
                'blocked_reason' => $reason,
            ]);

            $standby->update([
                'is_standby' => false,
                'is_visible' => true,
            ]);

            $log = SiteFailoverLog::create([
                'site_id'            => $site->id,
                'type'               => $type,
                'primary_id'         => $primary->id,
                'standby_id'         => $standby->id,
                'triggered_by'       => $triggeredBy,
                'trigger_reason'     => $reason,
                'trigger_identifier' => $identifier,
                'snapshot'           => $snapshot,
            ]);

            SyncPushService::push($site);

            return $log;
        });
    }

    /**
     * Rollback a failover: restore primary, demote standby back to pool.
     * @throws \RuntimeException
     */
    public static function rollback(SiteFailoverLog $log): SiteFailoverLog
    {
        if ($log->isRolledBack()) {
            throw new \RuntimeException("Failover log #{$log->id} already rolled back.");
        }

        [$model] = self::resolve($log->type);

        $primary = $model::findOrFail($log->primary_id);
        $standby = $model::findOrFail($log->standby_id);

        return DB::transaction(function () use ($log, $primary, $standby) {
            $before = $log->snapshot;

            $primary->update([
                'is_blocked'     => false,
                'is_visible'     => $before['primary_before']['is_visible'] ?? true,
                'blocked_reason' => null,
            ]);

            $standby->update([
                'is_standby' => true,
                'is_visible' => $before['standby_before']['is_visible'] ?? true,
            ]);

            $log->update(['rolled_back_at' => now()]);

            SyncPushService::push($log->site);

            return $log->fresh();
        });
    }

    /**
     * Restore the original primary when an external signal says it's active again.
     * Finds the latest active failover log for that primary and rolls it back.
     * If the primary is still blocked, unblocks it; active standby returns to pool.
     *
     * @throws \RuntimeException if no active failover found for this primary
     */
    public static function restore(Site $site, string $type, int $primaryId): SiteFailoverLog
    {
        $log = SiteFailoverLog::where('site_id', $site->id)
            ->where('type', $type)
            ->where('primary_id', $primaryId)
            ->whereNull('rolled_back_at')
            ->latest()
            ->first();

        if (!$log) {
            throw new \RuntimeException("No active failover log found for {$type} #{$primaryId}.");
        }

        return self::rollback($log);
    }

    /**
     * Block the currently active replacement and promote the next standby in priority order.
     * Used for cascade: Primary blocked → #1 active → #1 blocked → #2 active → …
     *
     * @throws \RuntimeException
     */
    public static function blockAndCascade(
        Site $site,
        string $type,
        int $activeId,
        string $reason = '[SIM] Cascade block',
        string $triggeredBy = 'manual',
    ): void {
        [$model] = self::resolve($type);

        $active = $model::where('site_id', $site->id)
            ->where('is_standby', false)
            ->where('is_blocked', false)
            ->findOrFail($activeId);

        $originalPrimaryId = $active->standby_for_id;
        if (!$originalPrimaryId) {
            throw new \RuntimeException("Record #{$activeId} has no original primary reference.");
        }

        // Next standby: still in standby pool for the same original primary, lowest sort_order
        $next = $model::where('site_id', $site->id)
            ->where('is_standby', true)
            ->where('is_blocked', false)
            ->where('standby_for_id', $originalPrimaryId)
            ->orderBy('sort_order')
            ->first();

        DB::transaction(function () use ($site, $type, $active, $next, $reason, $triggeredBy, $originalPrimaryId) {
            $active->update(['is_blocked' => true, 'is_visible' => false, 'blocked_reason' => $reason]);

            if ($next) {
                $next->update(['is_standby' => false, 'is_visible' => true]);

                SiteFailoverLog::create([
                    'site_id'        => $site->id,
                    'type'           => $type,
                    'primary_id'     => $originalPrimaryId,
                    'standby_id'     => $next->id,
                    'triggered_by'   => $triggeredBy,
                    'trigger_reason' => $reason,
                    'snapshot'       => [
                        'primary_before' => ['cascaded_from' => $active->id],
                        'standby_before' => $next->toArray(),
                    ],
                ]);
            }

            SyncPushService::push($site);
        });
    }

    /**
     * Full chain restore: unblock primary + reset every standby in its chain.
     * Handles cascaded failovers where multiple standbys were activated sequentially.
     *
     * @throws \RuntimeException
     */
    public static function restoreChain(Site $site, string $type, int $primaryId): void
    {
        [$model] = self::resolve($type);

        $primary = $model::where('site_id', $site->id)->findOrFail($primaryId);

        if (!$primary->is_blocked) {
            throw new \RuntimeException("Record #{$primaryId} is not blocked.");
        }

        DB::transaction(function () use ($site, $type, $primary, $model, $primaryId) {
            // Unblock original primary
            $primary->update(['is_blocked' => false, 'is_visible' => true, 'blocked_reason' => null]);

            // Reset all records in this chain: those promoted (is_standby=false) or blocked standbys
            $model::where('site_id', $site->id)
                ->where('standby_for_id', $primaryId)
                ->each(function ($record) {
                    $record->update([
                        'is_standby'  => true,
                        'is_blocked'  => false,
                        'is_visible'  => true,
                        'blocked_reason' => null,
                    ]);
                });

            // Mark all active failover logs for this primary as rolled back
            SiteFailoverLog::where('site_id', $site->id)
                ->where('type', $type)
                ->where('primary_id', $primaryId)
                ->whereNull('rolled_back_at')
                ->update(['rolled_back_at' => now()]);

            SyncPushService::push($site);
        });
    }

    /**
     * Get available standbys for a given primary record (for UI select).
     */
    public static function getAvailableStandbys(Site $site, string $type, int $primaryId): \Illuminate\Support\Collection
    {
        [$model] = self::resolve($type);

        return $model::where('site_id', $site->id)
            ->where('is_standby', true)
            ->where('is_blocked', false)
            ->where('is_visible', true)
            ->orderByRaw('standby_for_id = ? DESC', [$primaryId]) // paired first
            ->get();
    }

    public static function isMessenger(string $platform): bool
    {
        return in_array(strtolower($platform), self::MESSENGER_PLATFORMS, true);
    }

    // -------------------------------------------------------------------------

    private static function resolve(string $type): array
    {
        return match ($type) {
            'phone'  => [SitePhone::class,  'phones'],
            'social' => [SiteSocial::class, 'socials'],
            default  => throw new \InvalidArgumentException("Unknown failover type: {$type}"),
        };
    }
}
