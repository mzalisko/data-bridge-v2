<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_keys', function (Blueprint $table) {
            $table->json('permissions')->after('key_prefix')->nullable();
        });

        // Backfill existing rows with read-only defaults
        DB::table('api_keys')->whereNull('permissions')->update([
            'permissions' => json_encode(['phones.read', 'prices.read', 'addresses.read', 'socials.read']),
        ]);
    }

    public function down(): void
    {
        Schema::table('api_keys', function (Blueprint $table) {
            $table->dropColumn('permissions');
        });
    }
};
