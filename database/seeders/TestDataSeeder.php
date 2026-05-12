<?php

namespace Database\Seeders;

use App\Models\Site;
use App\Models\SiteAddress;
use App\Models\SiteGroup;
use App\Models\SitePhone;
use App\Models\SitePrice;
use App\Models\SiteSocial;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user (test credentials)
        User::create([
            'name'      => 'Test Admin',
            'email'     => 'admin@test.local',
            'password'  => Hash::make('test1234'),
            'role'      => 'admin',
            'is_active' => true,
        ]);

        // One group
        $group = SiteGroup::create([
            'name'        => 'Demo Group',
            'color'       => '#3b82f6',
            'description' => 'Test group',
        ]);

        // One site with fake data
        $site = Site::create([
            'name'        => 'Demo Site',
            'url'         => 'https://demo-site.example',
            'group_id'    => $group->id,
            'is_active'   => true,
            'active_geos' => ['UA', 'PL'],
        ]);

        // ── Phones: 3 primary, each with 1-2 standbys ──────────

        // Primary 1 — 2 standbys
        $ph1 = SitePhone::create([
            'site_id'    => $site->id, 'number' => '044 000-11-11',
            'dial_code'  => '380', 'label' => 'Головний',
            'geo_mode'   => 'all', 'sort_order' => 0, 'is_visible' => 1,
        ]);
        SitePhone::create([
            'site_id' => $site->id, 'number' => '067 111-22-33',
            'dial_code' => '380', 'is_standby' => 1,
            'standby_for_id' => $ph1->id, 'sort_order' => 1, 'is_visible' => 1,
        ]);
        SitePhone::create([
            'site_id' => $site->id, 'number' => '073 111-22-33',
            'dial_code' => '380', 'is_standby' => 1,
            'standby_for_id' => $ph1->id, 'sort_order' => 2, 'is_visible' => 1,
        ]);

        // Primary 2 — 1 standby
        $ph2 = SitePhone::create([
            'site_id'   => $site->id, 'number' => '+48 00 000 00 00',
            'dial_code' => '48', 'label' => 'Польща',
            'geo_mode'  => 'include', 'geo_countries' => ['PL'],
            'sort_order' => 3, 'is_visible' => 1,
        ]);
        SitePhone::create([
            'site_id' => $site->id, 'number' => '+48 99 999 99 99',
            'dial_code' => '48', 'is_standby' => 1,
            'standby_for_id' => $ph2->id, 'sort_order' => 4, 'is_visible' => 1,
        ]);

        // Primary 3 — no standbys (to test adding via swipe)
        SitePhone::create([
            'site_id'   => $site->id, 'number' => '050 222-33-44',
            'dial_code' => '380', 'geo_mode' => 'all',
            'sort_order' => 5, 'is_visible' => 1,
        ]);

        // Pool standby (unlinked — to test drag-to-link)
        SitePhone::create([
            'site_id'   => $site->id, 'number' => '063 000-00-00',
            'dial_code' => '380', 'is_standby' => 1,
            'label' => 'Пул', 'sort_order' => 6, 'is_visible' => 1,
        ]);

        // ── Socials ─────────────────────────────────────────────

        $tg = SiteSocial::create([
            'site_id' => $site->id, 'platform' => 'telegram',
            'handle' => '@demo_main', 'url' => 'https://t.me/demo_main',
            'sort_order' => 0, 'is_visible' => 1,
        ]);
        SiteSocial::create([
            'site_id' => $site->id, 'platform' => 'telegram',
            'handle' => '@demo_backup', 'url' => 'https://t.me/demo_backup',
            'sort_order' => 1, 'is_standby' => 1,
            'standby_for_id' => $tg->id, 'is_visible' => 1,
        ]);
        SiteSocial::create([
            'site_id' => $site->id, 'platform' => 'viber',
            'handle' => '+380001112222', 'url' => 'viber://chat?number=%2B380001112222',
            'sort_order' => 2, 'is_visible' => 1,
        ]);

        // ── Address + Price ──────────────────────────────────────

        SiteAddress::create([
            'site_id' => $site->id, 'city' => 'Test City',
            'street' => 'Demo Street 1', 'country_iso' => 'UA',
            'sort_order' => 0, 'is_visible' => 1,
        ]);
        SitePrice::create([
            'site_id' => $site->id, 'amount' => 100,
            'currency' => 'USD', 'label' => 'Demo Plan',
            'sort_order' => 0, 'is_visible' => 1,
        ]);

        $this->command->info('Seeded: 1 user, 1 group, 1 site with fake test data');
    }
}
