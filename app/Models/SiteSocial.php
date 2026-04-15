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
        'geo_mode',
        'geo_countries',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
