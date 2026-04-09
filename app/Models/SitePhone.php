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
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
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
