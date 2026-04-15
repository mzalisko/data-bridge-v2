<?php

namespace Database\Seeders;

use App\Models\Site;
use App\Models\SiteGroup;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Site Groups — firstOrCreate prevents duplicates on re-run
        $g1 = SiteGroup::firstOrCreate(['name' => 'Автосалони'],  ['color' => '#5288c1', 'description' => 'Автодилери та салони']);
        $g2 = SiteGroup::firstOrCreate(['name' => 'Нерухомість'], ['color' => '#48bb78', 'description' => 'Агентства нерухомості']);
        $g3 = SiteGroup::firstOrCreate(['name' => 'Медицина'],    ['color' => '#ed8936', 'description' => 'Клініки та лікарні']);

        // Sites
        Site::firstOrCreate(['url' => 'https://autosalon.kyiv.ua'], ['group_id' => $g1->id, 'name' => 'AutoSalon Kyiv',     'is_active' => true]);
        Site::firstOrCreate(['url' => 'https://carplaza.lviv.ua'],  ['group_id' => $g1->id, 'name' => 'CarPlaza Lviv',      'is_active' => true]);
        Site::firstOrCreate(['url' => 'https://motozone.com.ua'],   ['group_id' => $g1->id, 'name' => 'MotoZone',           'is_active' => false]);
        Site::firstOrCreate(['url' => 'https://realtyhub.ua'],      ['group_id' => $g2->id, 'name' => 'RealtyHub',          'is_active' => true]);
        Site::firstOrCreate(['url' => 'https://kvartiry.online'],   ['group_id' => $g2->id, 'name' => 'Квартири Онлайн',    'is_active' => true]);
        Site::firstOrCreate(['url' => 'https://medcenter.plus'],    ['group_id' => $g3->id, 'name' => 'MedCenter Plus',     'is_active' => true]);
        Site::firstOrCreate(['url' => 'https://smile-dent.ua'],     ['group_id' => $g3->id, 'name' => 'Стоматологія Смайл', 'is_active' => true]);

        // Extra users
        User::firstOrCreate(['email' => 'irina@databridge.local'],   ['name' => 'Ірина Коваль',   'password' => Hash::make('pass123'), 'role' => 'manager', 'is_active' => true]);
        User::firstOrCreate(['email' => 'oleksiy@databridge.local'], ['name' => 'Олексій Бондар', 'password' => Hash::make('pass123'), 'role' => 'viewer',  'is_active' => true]);
    }
}
