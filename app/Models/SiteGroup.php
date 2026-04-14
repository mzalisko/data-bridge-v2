<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SiteGroup extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'color',
        'icon',
    ];

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class, 'group_id');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(UserPermission::class, 'group_id');
    }

    public function activeSitesCount(): int
    {
        return $this->sites()->where('is_active', true)->count();
    }
}
