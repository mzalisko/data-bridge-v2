<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SitePrice extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'site_id',
        'label',
        'amount',
        'currency',
        'period',
        'is_visible',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'amount'     => 'decimal:2',
            'is_visible' => 'boolean',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
