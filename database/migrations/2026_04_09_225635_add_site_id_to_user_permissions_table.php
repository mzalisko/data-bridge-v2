<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::drop('user_permissions');

        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('group_id')->nullable()->constrained('site_groups')->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->cascadeOnDelete();
            $table->string('permission', 64);
            $table->boolean('granted')->default(true);

            $table->unique(['user_id', 'group_id', 'site_id', 'permission']);
        });
    }

    public function down(): void
    {
        Schema::drop('user_permissions');

        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('group_id')->nullable()->constrained('site_groups')->cascadeOnDelete();
            $table->string('permission', 64);
            $table->boolean('granted')->default(true);

            $table->unique(['user_id', 'group_id', 'permission']);
        });
    }
};
