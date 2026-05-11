<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'site_id', 'group_id', 'user_id',
        'source', 'entity_type', 'entity_id',
        'action', 'summary', 'snapshot',
    ];

    protected $casts = [
        'snapshot'   => 'array',
        'created_at' => 'datetime',
    ];

    public function site(): BelongsTo   { return $this->belongsTo(Site::class); }
    public function user(): BelongsTo   { return $this->belongsTo(User::class); }
}
