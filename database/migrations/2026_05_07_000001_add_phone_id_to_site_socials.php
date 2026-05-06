<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_socials', function (Blueprint $table) {
            $table->foreignId('phone_id')
                ->nullable()
                ->after('site_id')
                ->constrained('site_phones')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('site_socials', function (Blueprint $table) {
            $table->dropForeign(['phone_id']);
            $table->dropColumn('phone_id');
        });
    }
};
