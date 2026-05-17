<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->boolean('allow_plugin_edit')->default(false)->after('push_key');
            $table->string('plugin_edit_token', 64)->nullable()->after('allow_plugin_edit');
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn(['allow_plugin_edit', 'plugin_edit_token']);
        });
    }
};
