<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SitePhone extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'site_id',
        'label',
        'country_iso',
        'dial_code',
        'number',
        'is_primary',
        'is_visible',
        'sort_order',
        'geo_mode',
        'geo_countries',
    ];

    protected function casts(): array
    {
        return [
            'is_primary'    => 'boolean',
            'is_visible'    => 'boolean',
            'geo_countries' => 'array',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function fullNumber(): string
    {
        return '+' . $this->dial_code . ' ' . $this->number;
    }
}
