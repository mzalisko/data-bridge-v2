<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Site extends Model
{
    use HasFactory;
    protected $fillable = [
        'group_id',
        'name',
        'url',
        'description',
        'logo',
        'is_active',
        'site_countries',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function siteGroup(): BelongsTo
    {
        return $this->belongsTo(SiteGroup::class, 'group_id');
    }

    public function phones(): HasMany
    {
        return $this->hasMany(SitePhone::class)->orderBy('sort_order');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(SitePrice::class)->orderBy('sort_order');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(SiteAddress::class)->orderBy('sort_order');
    }

    public function socials(): HasMany
    {
        return $this->hasMany(SiteSocial::class)->orderBy('sort_order');
    }

    public function customFields(): HasMany
    {
        return $this->hasMany(SiteCustomField::class)->orderBy('sort_order');
    }

    public function apiKey(): HasOne
    {
        return $this->hasOne(ApiKey::class);
    }

    public function syncLogs(): HasMany
    {
        return $this->hasMany(SyncLog::class)->orderByDesc('synced_at');
    }

    public function lastSync(): ?SyncLog
    {
        return $this->syncLogs()->first();
    }

    public function latestSyncLog(): HasOne
    {
        return $this->hasOne(SyncLog::class)->latestOfMany();
    }
}
