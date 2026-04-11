<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password'     => 'hashed',
            'is_active'    => 'boolean',
            'locked_until' => 'datetime',
        ];
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(UserPermission::class);
    }

    public function favoriteSites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class, 'user_favorite_sites')
                    ->withTimestamps();
    }

    public function systemLogs(): HasMany
    {
        return $this->hasMany(SystemLog::class);
    }

    public function batchLogs(): HasMany
    {
        return $this->hasMany(BatchLog::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }
}
