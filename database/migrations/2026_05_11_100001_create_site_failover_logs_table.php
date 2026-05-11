<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_failover_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->enum('type', ['phone', 'social']);
            $table->unsignedBigInteger('primary_id');   // ID заблокованого запису
            $table->unsignedBigInteger('standby_id');   // ID резервного запису
            $table->enum('triggered_by', ['api', 'manual'])->default('api');
            $table->string('trigger_reason')->nullable(); // "WhatsApp blocked", "Number inactive"
            $table->string('trigger_identifier')->nullable(); // зовнішній ідентифікатор (api_signal_id)
            $table->json('snapshot');                   // стан обох записів до підміни
            $table->timestamp('rolled_back_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_failover_logs');
    }
};
