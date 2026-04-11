<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insertOrIgnore([
            'name'       => 'Admin',
            'email'      => 'admin@databridge.local',
            'password'   => Hash::make('admin123'),
            'role'       => 'admin',
            'is_active'  => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
