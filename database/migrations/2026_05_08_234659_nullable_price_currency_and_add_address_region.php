<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_prices', function (Blueprint $table) {
            $table->string('currency', 3)->nullable()->default(null)->change();
        });

        Schema::table('site_addresses', function (Blueprint $table) {
            $table->string('region', 100)->nullable()->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('site_prices', function (Blueprint $table) {
            $table->string('currency', 3)->nullable(false)->change();
        });

        Schema::table('site_addresses', function (Blueprint $table) {
            $table->dropColumn('region');
        });
    }
};
