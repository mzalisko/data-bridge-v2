<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Site;
use Illuminate\Database\Eloquent\Model;

class ActivityService
{
    public static function log(
        string $entityType,
        string $action,
        Model  $entity,
        string $summary,
        ?Site  $site = null,
    ): void {
        $siteId  = $site?->id ?? ($entity->site_id ?? null);
        $groupId = $site?->group_id ?? null;

        ActivityLog::create([
            'site_id'     => $siteId,
            'group_id'    => $groupId,
            'user_id'     => auth()->id(),
            'source'      => 'crm',
            'entity_type' => $entityType,
            'entity_id'   => $entity->id,
            'action'      => $action,
            'summary'     => $summary,
            'snapshot'    => $action === 'delete' ? $entity->toArray() : null,
        ]);
    }
}
