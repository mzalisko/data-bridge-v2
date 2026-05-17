<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_phones', function (Blueprint $table) {
            // Explicit pairing: which primary phone this standby covers.
            // null = general pool (covers any blocked phone of the same site).
            $table->unsignedBigInteger('standby_for_id')->nullable()->after('is_standby');
        });

        Schema::table('site_socials', function (Blueprint $table) {
            $table->unsignedBigInteger('standby_for_id')->nullable()->after('is_standby');
        });
    }

    public function down(): void
    {
        Schema::table('site_phones', function (Blueprint $table) {
            $table->dropColumn('standby_for_id');
        });

        Schema::table('site_socials', function (Blueprint $table) {
            $table->dropColumn('standby_for_id');
        });
    }
};
