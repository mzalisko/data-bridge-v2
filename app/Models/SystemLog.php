<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'event',
        'level',
        'context',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'context'    => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function write(string $event, string $level = 'info', array $context = []): void
    {
        static::create([
            'user_id'    => auth()->id(),
            'event'      => $event,
            'level'      => $level,
            'context'    => $context ?: null,
            'ip_address' => request()->ip(),
        ]);
    }
}
