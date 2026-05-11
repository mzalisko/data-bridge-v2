<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_phones', function (Blueprint $table) {
            $table->string('country_iso', 3)->nullable()->default(null)->change();
            $table->string('dial_code', 8)->nullable()->default(null)->change();
        });

        Schema::table('site_addresses', function (Blueprint $table) {
            $table->string('country_iso', 3)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('site_phones', function (Blueprint $table) {
            $table->string('country_iso', 3)->nullable(false)->change();
            $table->string('dial_code', 8)->nullable(false)->change();
        });

        Schema::table('site_addresses', function (Blueprint $table) {
            $table->string('country_iso', 3)->nullable(false)->change();
        });
    }
};
