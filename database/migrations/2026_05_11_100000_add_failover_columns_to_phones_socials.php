<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_phones', function (Blueprint $table) {
            $table->boolean('is_standby')->default(false)->after('is_primary');
            $table->boolean('is_blocked')->default(false)->after('is_standby');
            $table->string('blocked_reason')->nullable()->after('is_blocked');
        });

        Schema::table('site_socials', function (Blueprint $table) {
            $table->boolean('is_standby')->default(false)->after('is_visible');
            $table->boolean('is_blocked')->default(false)->after('is_standby');
            $table->string('blocked_reason')->nullable()->after('is_blocked');
        });
    }

    public function down(): void
    {
        Schema::table('site_phones', function (Blueprint $table) {
            $table->dropColumn(['is_standby', 'is_blocked', 'blocked_reason']);
        });

        Schema::table('site_socials', function (Blueprint $table) {
            $table->dropColumn(['is_standby', 'is_blocked', 'blocked_reason']);
        });
    }
};
