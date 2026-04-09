<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_phones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('label', 100)->nullable();
            $table->string('country_iso', 3);
            $table->string('dial_code', 8);
            $table->string('number', 32);
            $table->boolean('is_primary')->default(false);
            $table->smallInteger('sort_order')->default(0);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_phones');
    }
};
