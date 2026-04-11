<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteSocial extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'site_id',
        'platform',
        'handle',
        'url',
        'sort_order',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
