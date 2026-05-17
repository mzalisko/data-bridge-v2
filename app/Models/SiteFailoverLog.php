<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteFailoverLog extends Model
{
    protected $fillable = [
        'site_id',
        'type',
        'primary_id',
        'standby_id',
        'triggered_by',
        'trigger_reason',
        'trigger_identifier',
        'snapshot',
        'rolled_back_at',
    ];

    protected function casts(): array
    {
        return [
            'snapshot'       => 'array',
            'rolled_back_at' => 'datetime',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function isRolledBack(): bool
    {
        return $this->rolled_back_at !== null;
    }
}
