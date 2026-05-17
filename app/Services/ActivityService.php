<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Site;
use Illuminate\Database\Eloquent\Model;

class ActivityService
{
    // Fields to skip when building diffs (internal/meta, sensitive, or handled separately)
    private const SKIP_FIELDS = [
        'created_at', 'updated_at', 'site_id', 'group_id',
        'push_key', 'plugin_edit_token',   // sensitive credentials
        'active_geos', 'geo_rules',         // tracked separately via logGeo()
    ];

    public static function log(
        string $entityType,
        string $action,
        Model  $entity,
        string $summary,
        ?Site  $site = null,
        array  $before = [],
        string $source = 'crm',
    ): void {
        $siteId  = $site?->id ?? ($entity->site_id ?? null);
        $groupId = $site?->group_id ?? null;

        $snapshot = match($action) {
            'create' => ['after'  => self::clean($entity->toArray())],
            'update' => ['before' => self::clean($before), 'after' => self::clean($entity->toArray()), 'diff' => self::diff($before, $entity->toArray())],
            'delete' => ['before' => self::clean($entity->toArray())],
            default  => null,
        };

        ActivityLog::create([
            'site_id'     => $siteId,
            'group_id'    => $groupId,
            'user_id'     => auth()->id(),
            'source'      => $source,
            'entity_type' => $entityType,
            'entity_id'   => $entity->id,
            'action'      => $action,
            'summary'     => $summary,
            'snapshot'    => $snapshot,
        ]);
    }

    public static function logGeo(
        string $action,
        Site   $site,
        string $summary,
        array  $snapshot = [],
        string $source = 'crm',
    ): void {
        ActivityLog::create([
            'site_id'     => $site->id,
            'group_id'    => $site->group_id,
            'user_id'     => auth()->id(),
            'source'      => $source,
            'entity_type' => 'geo',
            'entity_id'   => $site->id,
            'action'      => $action,
            'summary'     => $summary,
            'snapshot'    => $snapshot ?: null,
        ]);
    }

    private static function clean(array $data): array
    {
        return collect($data)->except(self::SKIP_FIELDS)->all();
    }

    private static function diff(array $before, array $after): array
    {
        $changes = [];
        $skip    = array_merge(self::SKIP_FIELDS, ['id']);
        foreach ($after as $key => $newVal) {
            if (in_array($key, $skip)) continue;
            $oldVal = $before[$key] ?? null;
            if (json_encode($oldVal) !== json_encode($newVal)) {
                $changes[$key] = ['before' => $oldVal, 'after' => $newVal];
            }
        }
        return $changes;
    }
}
