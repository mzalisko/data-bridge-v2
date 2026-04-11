<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->timestamp('synced_at')->useCurrent();
            $table->string('status', 16);
            $table->smallInteger('duration_ms')->nullable();
            $table->string('checksum', 71)->nullable();
            $table->string('error_msg')->nullable();

            $table->index(['site_id', 'synced_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
    }
};
