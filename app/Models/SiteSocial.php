<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\SitePhone;

class SiteSocial extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'site_id',
        'phone_id',
        'platform',
        'handle',
        'url',
        'is_visible',
        'sort_order',
        'geo_mode',
        'geo_countries',
    ];

    protected function casts(): array
    {
        return [
            'is_visible'    => 'boolean',
            'geo_countries' => 'array',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function phone(): BelongsTo
    {
        return $this->belongsTo(SitePhone::class, 'phone_id');
    }
}
