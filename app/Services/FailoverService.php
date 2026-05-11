<?php

namespace App\Services;

use App\Models\Site;
use App\Models\SitePhone;
use App\Models\SiteSocial;
use App\Models\SiteFailoverLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FailoverService
{
    /**
     * Trigger failover: mark primary as blocked, activate standby.
     *
     * @param  Site   $site
     * @param  string $type            'phone' | 'social'
     * @param  int    $primaryId       ID of the blocked record
     * @param  string $reason          Human-readable reason ("WhatsApp banned", etc.)
     * @param  string $triggeredBy     'api' | 'manual'
     * @param  string|null $identifier External signal identifier for traceability
     * @return SiteFailoverLog
     *
     * @throws \RuntimeException  if no standby record exists for the site+type
     * @throws \RuntimeException  if primary is already blocked
     */
    public static function trigger(
        Site $site,
        string $type,
        int $primaryId,
        string $reason,
        string $triggeredBy = 'api',
        ?string $identifier = null,
    ): SiteFailoverLog {
        [$model, $relation] = self::resolve($type);

        $primary = $model::where('site_id', $site->id)->findOrFail($primaryId);

        if ($primary->is_blocked) {
            throw new \RuntimeException("Record #{$primaryId} is already blocked.");
        }

        $standby = $model::where('site_id', $site->id)
            ->where('is_standby', true)
            ->where('is_blocked', false)
            ->where('is_visible', true)
            ->first();

        if (!$standby) {
            throw new \RuntimeException("No available standby {$type} for site #{$site->id}.");
        }

        return DB::transaction(function () use ($site, $type, $primary, $standby, $reason, $triggeredBy, $identifier) {
            $snapshot = [
                'primary_before' => $primary->toArray(),
                'standby_before' => $standby->toArray(),
            ];

            // Mark primary blocked + hidden
            $primary->update([
                'is_blocked'     => true,
                'is_visible'     => false,
                'blocked_reason' => $reason,
            ]);

            // Promote standby: move it out of standby pool into active position
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
     *
     * @param  SiteFailoverLog $log
     * @return SiteFailoverLog
     *
     * @throws \RuntimeException  if already rolled back
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

            // Restore primary to pre-failover state (unblock, restore visibility)
            $primary->update([
                'is_blocked'     => false,
                'is_visible'     => $before['primary_before']['is_visible'] ?? true,
                'blocked_reason' => null,
            ]);

            // Return standby to pool
            $standby->update([
                'is_standby' => true,
                'is_visible' => $before['standby_before']['is_visible'] ?? true,
            ]);

            $log->update(['rolled_back_at' => now()]);

            SyncPushService::push($log->site);

            return $log->fresh();
        });
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
