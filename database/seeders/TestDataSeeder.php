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
        // Site Groups
        $g1 = SiteGroup::create(['name' => 'Автосалони',   'color' => '#5288c1', 'description' => 'Автодилери та салони']);
        $g2 = SiteGroup::create(['name' => 'Нерухомість',  'color' => '#48bb78', 'description' => 'Агентства нерухомості']);
        $g3 = SiteGroup::create(['name' => 'Медицина',     'color' => '#ed8936', 'description' => 'Клініки та лікарні']);

        // Sites — group 1
        Site::create(['group_id' => $g1->id, 'name' => 'AutoSalon Kyiv',      'url' => 'https://autosalon.kyiv.ua',   'is_active' => true]);
        Site::create(['group_id' => $g1->id, 'name' => 'CarPlaza Lviv',       'url' => 'https://carplaza.lviv.ua',    'is_active' => true]);
        Site::create(['group_id' => $g1->id, 'name' => 'MotoZone',            'url' => 'https://motozone.com.ua',     'is_active' => false]);

        // Sites — group 2
        Site::create(['group_id' => $g2->id, 'name' => 'RealtyHub',           'url' => 'https://realtyhub.ua',        'is_active' => true]);
        Site::create(['group_id' => $g2->id, 'name' => 'Квартири Онлайн',     'url' => 'https://kvartiry.online',     'is_active' => true]);

        // Sites — group 3
        Site::create(['group_id' => $g3->id, 'name' => 'MedCenter Plus',      'url' => 'https://medcenter.plus',      'is_active' => true]);
        Site::create(['group_id' => $g3->id, 'name' => 'Стоматологія Смайл',  'url' => 'https://smile-dent.ua',       'is_active' => true]);

        // Extra users
        User::create(['name' => 'Ірина Коваль',   'email' => 'irina@databridge.local',   'password' => Hash::make('pass123'), 'role' => 'manager', 'is_active' => true]);
        User::create(['name' => 'Олексій Бондар', 'email' => 'oleksiy@databridge.local', 'password' => Hash::make('pass123'), 'role' => 'viewer',  'is_active' => true]);
    }
}
