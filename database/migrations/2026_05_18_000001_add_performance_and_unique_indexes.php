<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit 2026-05-18 (DB-C2 / DB-H1 / DB-H2 / DB-M3):
 *  - unique on bearer-secret lookup columns (push_key, plugin_edit_token)
 *  - composite (site_id, sort_order) on child tables — every relation orders by it
 *  - index updated_at — delta sync filters on it (?since=)
 *  - index failover linkage columns (standby_for_id, primary_id, standby_id)
 */
return new class extends Migration
{
    private array $childTables = [
        'site_phones',
        'site_prices',
        'site_addresses',
        'site_socials',
        'site_custom_fields',
    ];

    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->unique('push_key');
            $table->unique('plugin_edit_token');
        });

        foreach ($this->childTables as $name) {
            Schema::table($name, function (Blueprint $table) use ($name) {
                $table->index(['site_id', 'sort_order'], "{$name}_site_sort_idx");
                $table->index('updated_at', "{$name}_updated_at_idx");
            });
        }

        Schema::table('site_phones', function (Blueprint $table) {
            $table->index('standby_for_id', 'site_phones_standby_for_idx');
        });
        Schema::table('site_socials', function (Blueprint $table) {
            $table->index('standby_for_id', 'site_socials_standby_for_idx');
        });

        Schema::table('site_failover_logs', function (Blueprint $table) {
            $table->index('primary_id', 'failover_logs_primary_idx');
            $table->index('standby_id', 'failover_logs_standby_idx');
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropUnique('sites_push_key_unique');
            $table->dropUnique('sites_plugin_edit_token_unique');
        });

        foreach ($this->childTables as $name) {
            Schema::table($name, function (Blueprint $table) use ($name) {
                $table->dropIndex("{$name}_site_sort_idx");
                $table->dropIndex("{$name}_updated_at_idx");
            });
        }

        Schema::table('site_phones', function (Blueprint $table) {
            $table->dropIndex('site_phones_standby_for_idx');
        });
        Schema::table('site_socials', function (Blueprint $table) {
            $table->dropIndex('site_socials_standby_for_idx');
        });

        Schema::table('site_failover_logs', function (Blueprint $table) {
            $table->dropIndex('failover_logs_primary_idx');
            $table->dropIndex('failover_logs_standby_idx');
        });
    }
};
