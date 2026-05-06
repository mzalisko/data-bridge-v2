<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteAddress extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'site_id',
        'label',
        'country_iso',
        'city',
        'street',
        'building',
        'postal_code',
        'latitude',
        'longitude',
        'is_primary',
        'is_visible',
        'sort_order',
        'geo_mode',
        'geo_countries',
    ];

    protected function casts(): array
    {
        return [
            'latitude'      => 'decimal:7',
            'longitude'     => 'decimal:7',
            'is_primary'    => 'boolean',
            'is_visible'    => 'boolean',
            'geo_countries' => 'array',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
