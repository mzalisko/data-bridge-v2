<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->json('active_geos')->nullable()->after('plugin_webhook_url');
            $table->json('geo_rules')->nullable()->after('active_geos');
        });

        Schema::table('site_phones', function (Blueprint $table) {
            if (!Schema::hasColumn('site_phones', 'is_visible')) {
                $table->boolean('is_visible')->default(true)->after('is_primary');
            }
        });
        Schema::table('site_addresses', function (Blueprint $table) {
            if (!Schema::hasColumn('site_addresses', 'is_visible')) {
                $table->boolean('is_visible')->default(true)->after('is_primary');
            }
        });
        Schema::table('site_socials', function (Blueprint $table) {
            if (!Schema::hasColumn('site_socials', 'is_visible')) {
                $table->boolean('is_visible')->default(true)->after('url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn(['active_geos', 'geo_rules']);
        });
        Schema::table('site_phones',    fn (Blueprint $t) => $t->dropColumn('is_visible'));
        Schema::table('site_addresses', fn (Blueprint $t) => $t->dropColumn('is_visible'));
        Schema::table('site_socials',   fn (Blueprint $t) => $t->dropColumn('is_visible'));
    }
};
