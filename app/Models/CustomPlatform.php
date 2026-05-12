<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CustomPlatform extends Model
{
    protected $fillable = ['slug', 'label', 'category'];

    private static ?array $_messengerCache = null;

    public static function messengerSlugs(): array
    {
        if (self::$_messengerCache === null) {
            $custom = static::where('category', 'messenger')->pluck('slug')->toArray();
            self::$_messengerCache = array_merge(['telegram', 'whatsapp', 'viber'], $custom);
        }
        return self::$_messengerCache;
    }

    public static function messengerOptions(): array
    {
        $defaults = ['telegram' => 'Telegram', 'whatsapp' => 'WhatsApp', 'viber' => 'Viber'];
        $custom   = static::where('category', 'messenger')->pluck('label', 'slug')->toArray();
        return $defaults + $custom;
    }

    public static function fromLabel(string $label, string $category = 'messenger'): static
    {
        $slug = Str::slug($label, '_');
        return static::firstOrCreate(
            ['slug' => $slug],
            ['label' => trim($label), 'category' => $category]
        );
    }
}
