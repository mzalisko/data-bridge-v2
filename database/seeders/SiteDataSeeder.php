<?php

namespace Database\Seeders;

use App\Models\Site;
use App\Models\SiteGroup;
use App\Models\SitePhone;
use App\Models\SitePrice;
use App\Models\SiteAddress;
use App\Models\SiteSocial;
use Illuminate\Database\Seeder;

class SiteDataSeeder extends Seeder
{
    public function run(): void
    {
        // ── Groups ──────────────────────────────────────────────────────
        $g1 = SiteGroup::firstOrCreate(['name' => 'Group Alpha'], [
            'color' => '#6366f1', 'description' => 'Alpha cluster',
        ]);
        $g2 = SiteGroup::firstOrCreate(['name' => 'Group Beta'], [
            'color' => '#48bb78', 'description' => 'Beta cluster',
        ]);
        $g3 = SiteGroup::firstOrCreate(['name' => 'Group Gamma'], [
            'color' => '#ed8936', 'description' => 'Gamma cluster',
        ]);

        // ── Sites ────────────────────────────────────────────────────────
        $sites = [
            // Alpha (3 sites)
            ['name' => 'Site1', 'url' => 'https://site1.example.com',  'group_id' => $g1->id],
            ['name' => 'Site2', 'url' => 'https://site2.example.com',  'group_id' => $g1->id],
            ['name' => 'Site3', 'url' => 'https://site3.example.com',  'group_id' => $g1->id],
            // Beta (2 sites)
            ['name' => 'Site4', 'url' => 'https://site4.example.com',  'group_id' => $g2->id],
            ['name' => 'Site5', 'url' => 'https://site5.example.com',  'group_id' => $g2->id],
            // Gamma (3 sites)
            ['name' => 'Site6', 'url' => 'https://site6.example.com',  'group_id' => $g3->id],
            ['name' => 'Site7', 'url' => 'https://site7.example.com',  'group_id' => $g3->id],
            ['name' => 'Site8', 'url' => 'https://site8.example.com',  'group_id' => $g3->id],
        ];

        $created = [];
        foreach ($sites as $s) {
            $created[$s['name']] = Site::firstOrCreate(
                ['url' => $s['url']],
                ['name' => $s['name'], 'group_id' => $s['group_id'], 'is_active' => true]
            );
        }

        // ── Phones ───────────────────────────────────────────────────────
        $phones = [
            ['site' => 'Site1', 'number' => '0441234567', 'country_iso' => 'UA', 'dial_code' => '380', 'label' => 'Main',      'is_primary' => true],
            ['site' => 'Site1', 'number' => '0501112233', 'country_iso' => 'UA', 'dial_code' => '380', 'label' => 'Support',   'is_primary' => false],
            ['site' => 'Site2', 'number' => '0442345678', 'country_iso' => 'UA', 'dial_code' => '380', 'label' => 'Main',      'is_primary' => true],
            ['site' => 'Site3', 'number' => '0443456789', 'country_iso' => 'UA', 'dial_code' => '380', 'label' => 'Main',      'is_primary' => true],
            ['site' => 'Site3', 'number' => '0667778899', 'country_iso' => 'UA', 'dial_code' => '380', 'label' => 'Hotline',   'is_primary' => false],
            ['site' => 'Site4', 'number' => '0444567890', 'country_iso' => 'UA', 'dial_code' => '380', 'label' => 'Main',      'is_primary' => true],
            ['site' => 'Site5', 'number' => '0445678901', 'country_iso' => 'UA', 'dial_code' => '380', 'label' => 'Main',      'is_primary' => true],
            ['site' => 'Site5', 'number' => '0993334455', 'country_iso' => 'UA', 'dial_code' => '380', 'label' => 'Sales',     'is_primary' => false],
            ['site' => 'Site6', 'number' => '0446789012', 'country_iso' => 'UA', 'dial_code' => '380', 'label' => 'Main',      'is_primary' => true],
            ['site' => 'Site7', 'number' => '0447890123', 'country_iso' => 'UA', 'dial_code' => '380', 'label' => 'Main',      'is_primary' => true],
            ['site' => 'Site8', 'number' => '0448901234', 'country_iso' => 'UA', 'dial_code' => '380', 'label' => 'Main',      'is_primary' => true],
            ['site' => 'Site8', 'number' => '0731234567', 'country_iso' => 'UA', 'dial_code' => '380', 'label' => 'Support',   'is_primary' => false],
        ];
        foreach ($phones as $p) {
            $site = $created[$p['site']] ?? null;
            if (!$site) continue;
            SitePhone::firstOrCreate(
                ['site_id' => $site->id, 'number' => $p['number']],
                ['country_iso' => $p['country_iso'], 'dial_code' => $p['dial_code'], 'label' => $p['label'], 'is_primary' => $p['is_primary']]
            );
        }

        // ── Prices ───────────────────────────────────────────────────────
        $prices = [
            ['site' => 'Site1', 'amount' => '1200.00', 'currency' => 'UAH', 'label' => 'Basic',    'is_visible' => true],
            ['site' => 'Site1', 'amount' => '45.00',   'currency' => 'USD', 'label' => 'Premium',  'is_visible' => true],
            ['site' => 'Site2', 'amount' => '950.00',  'currency' => 'UAH', 'label' => 'Basic',    'is_visible' => true],
            ['site' => 'Site3', 'amount' => '2400.00', 'currency' => 'UAH', 'label' => 'Standard', 'is_visible' => true],
            ['site' => 'Site4', 'amount' => '800.00',  'currency' => 'UAH', 'label' => 'Basic',    'is_visible' => true],
            ['site' => 'Site4', 'amount' => '30.00',   'currency' => 'EUR', 'label' => 'Pro',      'is_visible' => true],
            ['site' => 'Site5', 'amount' => '1500.00', 'currency' => 'UAH', 'label' => 'Standard', 'is_visible' => true],
            ['site' => 'Site6', 'amount' => '1100.00', 'currency' => 'UAH', 'label' => 'Basic',    'is_visible' => true],
            ['site' => 'Site7', 'amount' => '1800.00', 'currency' => 'UAH', 'label' => 'Standard', 'is_visible' => true],
            ['site' => 'Site7', 'amount' => '65.00',   'currency' => 'USD', 'label' => 'Premium',  'is_visible' => false],
            ['site' => 'Site8', 'amount' => '700.00',  'currency' => 'UAH', 'label' => 'Basic',    'is_visible' => true],
        ];
        foreach ($prices as $p) {
            $site = $created[$p['site']] ?? null;
            if (!$site) continue;
            SitePrice::firstOrCreate(
                ['site_id' => $site->id, 'label' => $p['label'], 'currency' => $p['currency']],
                ['amount' => $p['amount'], 'is_visible' => $p['is_visible']]
            );
        }

        // ── Addresses ────────────────────────────────────────────────────
        $addresses = [
            ['site' => 'Site1', 'country_iso' => 'UA', 'city' => 'Kyiv',       'street' => 'Khreshchatyk St',   'building' => '1',  'label' => 'Main office'],
            ['site' => 'Site2', 'country_iso' => 'UA', 'city' => 'Lviv',       'street' => 'Svobody Ave',        'building' => '14', 'label' => 'Main office'],
            ['site' => 'Site3', 'country_iso' => 'UA', 'city' => 'Odesa',      'street' => 'Derybasivska St',    'building' => '7',  'label' => 'Main office'],
            ['site' => 'Site3', 'country_iso' => 'UA', 'city' => 'Odesa',      'street' => 'Pushkinska St',      'building' => '21', 'label' => 'Branch'],
            ['site' => 'Site4', 'country_iso' => 'UA', 'city' => 'Dnipro',     'street' => 'Haharina Ave',       'building' => '3',  'label' => 'Main office'],
            ['site' => 'Site5', 'country_iso' => 'UA', 'city' => 'Kharkiv',    'street' => 'Sumska St',          'building' => '42', 'label' => 'Main office'],
            ['site' => 'Site6', 'country_iso' => 'UA', 'city' => 'Zaporizhzhia','street' => 'Soborna St',        'building' => '5',  'label' => 'Main office'],
            ['site' => 'Site7', 'country_iso' => 'UA', 'city' => 'Vinnytsia',  'street' => 'Soborna St',         'building' => '9',  'label' => 'Main office'],
            ['site' => 'Site8', 'country_iso' => 'UA', 'city' => 'Poltava',    'street' => 'Zhovtneva St',       'building' => '18', 'label' => 'Main office'],
        ];
        foreach ($addresses as $a) {
            $site = $created[$a['site']] ?? null;
            if (!$site) continue;
            SiteAddress::firstOrCreate(
                ['site_id' => $site->id, 'city' => $a['city'], 'label' => $a['label']],
                ['country_iso' => $a['country_iso'], 'street' => $a['street'], 'building' => $a['building']]
            );
        }

        // ── Socials ──────────────────────────────────────────────────────
        $socials = [
            ['site' => 'Site1', 'platform' => 'instagram', 'handle' => '@site1',    'url' => 'https://instagram.com/site1'],
            ['site' => 'Site1', 'platform' => 'facebook',  'handle' => 'site1ua',   'url' => 'https://facebook.com/site1ua'],
            ['site' => 'Site2', 'platform' => 'instagram', 'handle' => '@site2',    'url' => 'https://instagram.com/site2'],
            ['site' => 'Site2', 'platform' => 'telegram',  'handle' => '@site2bot', 'url' => 'https://t.me/site2bot'],
            ['site' => 'Site3', 'platform' => 'facebook',  'handle' => 'site3ua',   'url' => 'https://facebook.com/site3ua'],
            ['site' => 'Site4', 'platform' => 'instagram', 'handle' => '@site4',    'url' => 'https://instagram.com/site4'],
            ['site' => 'Site4', 'platform' => 'youtube',   'handle' => 'site4ch',   'url' => 'https://youtube.com/@site4ch'],
            ['site' => 'Site5', 'platform' => 'telegram',  'handle' => '@site5',    'url' => 'https://t.me/site5'],
            ['site' => 'Site6', 'platform' => 'instagram', 'handle' => '@site6',    'url' => 'https://instagram.com/site6'],
            ['site' => 'Site6', 'platform' => 'facebook',  'handle' => 'site6ua',   'url' => 'https://facebook.com/site6ua'],
            ['site' => 'Site7', 'platform' => 'tiktok',    'handle' => '@site7',    'url' => 'https://tiktok.com/@site7'],
            ['site' => 'Site8', 'platform' => 'instagram', 'handle' => '@site8',    'url' => 'https://instagram.com/site8'],
            ['site' => 'Site8', 'platform' => 'viber',     'handle' => '+380441234567', 'url' => 'viber://chat?number=380441234567'],
        ];
        foreach ($socials as $s) {
            $site = $created[$s['site']] ?? null;
            if (!$site) continue;
            SiteSocial::firstOrCreate(
                ['site_id' => $site->id, 'platform' => $s['platform']],
                ['handle' => $s['handle'], 'url' => $s['url']]
            );
        }
    }
}
