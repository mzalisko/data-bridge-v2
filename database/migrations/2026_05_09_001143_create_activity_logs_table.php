<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('group_id')->nullable()->constrained('site_groups')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source', 16)->default('crm'); // crm|plugin|api
            $table->string('entity_type', 32); // phone|price|address|social|field|site
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('action', 16); // create|update|delete|restore
            $table->string('summary', 255)->nullable(); // human-readable line
            $table->json('snapshot')->nullable(); // full row snapshot for restore
            $table->timestamp('created_at')->useCurrent();
            $table->index(['site_id', 'created_at']);
            $table->index(['group_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
