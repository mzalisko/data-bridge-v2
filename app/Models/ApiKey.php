<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class ApiKey extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'site_id',
        'key_hash',
        'key_prefix',
        'last_used',
        'revoked_at',
    ];

    protected $hidden = ['key_hash'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'last_used'  => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    public function isActive(): bool
    {
        return ! $this->isRevoked();
    }

    public function verify(string $rawKey): bool
    {
        return Hash::check($rawKey, $this->key_hash);
    }

    public static function generate(): array
    {
        $raw    = 'dbapi_' . bin2hex(random_bytes(16));
        $prefix = substr($raw, 0, 12);

        return [
            'raw'    => $raw,
            'hash'   => Hash::make($raw),
            'prefix' => $prefix,
        ];
    }
}
