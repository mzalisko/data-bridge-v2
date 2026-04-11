<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Site;

class UserPermission extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'group_id',
        'site_id',
        'permission',
        'granted',
    ];

    protected function casts(): array
    {
        return [
            'granted' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function siteGroup(): BelongsTo
    {
        return $this->belongsTo(SiteGroup::class, 'group_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
