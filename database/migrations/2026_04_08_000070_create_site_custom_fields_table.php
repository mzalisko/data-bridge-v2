<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('field_key', 128);
            $table->text('field_value');
            $table->string('field_type', 32)->default('text');
            $table->smallInteger('sort_order')->default(0);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['site_id', 'field_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_custom_fields');
    }
};
