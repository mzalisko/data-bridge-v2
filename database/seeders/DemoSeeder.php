<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // Groups
        $groups = [
            ['name' => 'IT-сервіси',       'color' => '#818cf8', 'description' => 'Хмарні та API сервіси'],
            ['name' => 'Медичні платформи', 'color' => '#34d399', 'description' => 'Медичні та health-сервіси'],
            ['name' => 'E-commerce',        'color' => '#f59e0b', 'description' => 'Інтернет-магазини'],
        ];

        foreach ($groups as &$g) {
            $g['created_at'] = $now;
            $g['updated_at'] = $now;
        }

        DB::table('site_groups')->insert($groups);

        $groupIds = DB::table('site_groups')->pluck('id', 'name');

        // Sites
        $sites = [
            ['group_id' => $groupIds['IT-сервіси'],       'name' => 'CloudBase API',   'url' => 'https://cloudbase.example.com', 'is_active' => true],
            ['group_id' => $groupIds['IT-сервіси'],       'name' => 'DataSync Pro',    'url' => 'https://datasync.example.com',  'is_active' => true],
            ['group_id' => $groupIds['IT-сервіси'],       'name' => 'NexusPanel',      'url' => 'https://nexus.example.com',     'is_active' => false],
            ['group_id' => $groupIds['Медичні платформи'],'name' => 'HealthBridge API','url' => 'https://healthbridge.example.com','is_active' => true],
            ['group_id' => $groupIds['Медичні платформи'],'name' => 'MedTrack System', 'url' => 'https://medtrack.example.com',  'is_active' => true],
            ['group_id' => $groupIds['E-commerce'],       'name' => 'ShopCore',        'url' => 'https://shopcore.example.com',  'is_active' => true],
            ['group_id' => $groupIds['E-commerce'],       'name' => 'MarketHub',       'url' => 'https://markethub.example.com', 'is_active' => false],
        ];

        foreach ($sites as &$s) {
            $s['created_at'] = $now;
            $s['updated_at'] = $now;
        }

        DB::table('sites')->insert($sites);

        // Extra users
        DB::table('users')->insertOrIgnore([
            [
                'name'       => 'Manager Олег',
                'email'      => 'manager@databridge.local',
                'password'   => Hash::make('manager123'),
                'role'       => 'manager',
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Viewer Аня',
                'email'      => 'viewer@databridge.local',
                'password'   => Hash::make('viewer123'),
                'role'       => 'viewer',
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        // System logs
        $levels = ['info', 'info', 'info', 'warning', 'error'];
        $events = ['Користувач увійшов', 'Сайт додано', 'Синхронізація завершена', 'Помилка підключення', 'API ключ відкликано'];
        $adminId = DB::table('users')->where('email', 'admin@databridge.local')->value('id');

        for ($i = 0; $i < 15; $i++) {
            DB::table('system_logs')->insert([
                'user_id'    => $adminId,
                'level'      => $levels[$i % 5],
                'event'      => $events[$i % 5],
                'ip_address' => '127.0.0.1',
                'created_at' => $now->copy()->subMinutes($i * 7),
            ]);
        }
    }
}
