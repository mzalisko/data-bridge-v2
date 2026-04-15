<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['site_phones', 'site_prices', 'site_addresses', 'site_socials'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('geo_mode', 10)->nullable()->after('sort_order');
                $table->string('geo_countries', 255)->nullable()->after('geo_mode');
            });
        }
    }

    public function down(): void
    {
        foreach (['site_phones', 'site_prices', 'site_addresses', 'site_socials'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn(['geo_mode', 'geo_countries']);
            });
        }
    }
};
