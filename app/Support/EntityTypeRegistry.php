<?php

namespace App\Support;

use App\Models\SiteAddress;
use App\Models\SiteCustomField;
use App\Models\SitePhone;
use App\Models\SitePrice;
use App\Models\SiteSocial;
use InvalidArgumentException;

/**
 * Single source for "site data type string -> model + Site relation"
 * (audit S6 — this map was re-implemented in 5 places with subtly
 * different vocabularies: singular, plural, the 'messengers' alias,
 * and 'field'/'fields'/'custom_field'). Accepts every variant the old
 * call sites accepted so it is a faithful drop-in. Behaviour parity:
 * use model()/resolve() (nullable) where the caller guarded with a
 * null check or abort(); use *OrFail() where it relied on match()
 * throwing on an unknown type.
 */
class EntityTypeRegistry
{
    /** canonical singular => [model class, Site relation method] */
    private const TYPES = [
        'phone'   => [SitePhone::class,       'phones'],
        'price'   => [SitePrice::class,       'prices'],
        'address' => [SiteAddress::class,     'addresses'],
        'social'  => [SiteSocial::class,      'socials'],
        'field'   => [SiteCustomField::class, 'customFields'],
    ];

    /** every other accepted spelling => canonical */
    private const ALIASES = [
        'phones'        => 'phone',
        'prices'        => 'price',
        'addresses'     => 'address',
        'socials'       => 'social',
        'messengers'    => 'social',
        'fields'        => 'field',
        'custom_field'  => 'field',
        'custom_fields' => 'field',
    ];

    public static function canonical(string $type): ?string
    {
        if (isset(self::TYPES[$type])) {
            return $type;
        }

        return self::ALIASES[$type] ?? null;
    }

    /** [model class, relation] or null for an unknown type. */
    public static function resolve(string $type): ?array
    {
        $key = self::canonical($type);

        return $key ? self::TYPES[$key] : null;
    }

    public static function resolveOrFail(string $type): array
    {
        return self::resolve($type)
            ?? throw new InvalidArgumentException("Unknown entity type: {$type}");
    }

    /** Model class string or null for an unknown type. */
    public static function model(string $type): ?string
    {
        return self::resolve($type)[0] ?? null;
    }

    public static function modelOrFail(string $type): string
    {
        return self::resolveOrFail($type)[0];
    }

    public static function relation(string $type): ?string
    {
        return self::resolve($type)[1] ?? null;
    }
}
