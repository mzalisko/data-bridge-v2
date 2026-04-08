<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteCustomField extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'site_id',
        'field_key',
        'field_value',
        'field_type',
        'sort_order',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
