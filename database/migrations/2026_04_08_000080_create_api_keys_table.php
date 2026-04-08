<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('key_hash');
            $table->string('key_prefix', 12);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('last_used')->nullable();
            $table->timestamp('revoked_at')->nullable();

            $table->index('key_prefix');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
