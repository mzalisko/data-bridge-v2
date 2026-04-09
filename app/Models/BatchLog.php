<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'data_type',
        'affected_sites',
        'change_delta',
    ];

    protected function casts(): array
    {
        return [
            'affected_sites' => 'array',
            'change_delta'   => 'array',
            'created_at'     => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
