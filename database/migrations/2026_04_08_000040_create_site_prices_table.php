<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3);
            $table->string('period', 32)->nullable();
            $table->boolean('is_visible')->default(true);
            $table->smallInteger('sort_order')->default(0);
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_prices');
    }
};
