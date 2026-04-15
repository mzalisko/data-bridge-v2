<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;

class CountriesSeeder extends Seeder
{
    public function run(): void
    {
        $countries = [
            ['iso' => 'UA', 'dial_code' => '380', 'name' => 'Україна',        'sort_order' => 1],
            ['iso' => 'PL', 'dial_code' => '48',  'name' => 'Польща',         'sort_order' => 2],
            ['iso' => 'DE', 'dial_code' => '49',  'name' => 'Німеччина',      'sort_order' => 3],
            ['iso' => 'GB', 'dial_code' => '44',  'name' => 'Велика Британія','sort_order' => 4],
            ['iso' => 'US', 'dial_code' => '1',   'name' => 'США',            'sort_order' => 5],
            ['iso' => 'FR', 'dial_code' => '33',  'name' => 'Франція',        'sort_order' => 6],
            ['iso' => 'IT', 'dial_code' => '39',  'name' => 'Італія',         'sort_order' => 7],
            ['iso' => 'ES', 'dial_code' => '34',  'name' => 'Іспанія',        'sort_order' => 8],
            ['iso' => 'CZ', 'dial_code' => '420', 'name' => 'Чехія',          'sort_order' => 9],
            ['iso' => 'RO', 'dial_code' => '40',  'name' => 'Румунія',        'sort_order' => 10],
            ['iso' => 'SK', 'dial_code' => '421', 'name' => 'Словаччина',     'sort_order' => 11],
            ['iso' => 'HU', 'dial_code' => '36',  'name' => 'Угорщина',       'sort_order' => 12],
            ['iso' => 'BY', 'dial_code' => '375', 'name' => 'Білорусь',       'sort_order' => 13],
            ['iso' => 'MD', 'dial_code' => '373', 'name' => 'Молдова',        'sort_order' => 14],
            ['iso' => 'GE', 'dial_code' => '995', 'name' => 'Грузія',         'sort_order' => 15],
            ['iso' => 'LT', 'dial_code' => '370', 'name' => 'Литва',          'sort_order' => 16],
            ['iso' => 'LV', 'dial_code' => '371', 'name' => 'Латвія',         'sort_order' => 17],
            ['iso' => 'EE', 'dial_code' => '372', 'name' => 'Естонія',        'sort_order' => 18],
            ['iso' => 'NL', 'dial_code' => '31',  'name' => 'Нідерланди',     'sort_order' => 19],
            ['iso' => 'AT', 'dial_code' => '43',  'name' => 'Австрія',        'sort_order' => 20],
        ];

        foreach ($countries as $data) {
            Country::firstOrCreate(['iso' => $data['iso']], $data);
        }
    }
}
