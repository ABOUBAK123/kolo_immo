<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->json('filters');                           // city, type, price_min, price_max, guests
            $table->enum('frequency', ['daily', 'weekly'])->default('daily');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamps();
        });

        // Track which properties were already sent for each alert (avoid duplicates)
        Schema::create('search_alert_sent', function (Blueprint $table) {
            $table->foreignId('search_alert_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->primary(['search_alert_id', 'property_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_alert_sent');
        Schema::dropIfExists('search_alerts');
    }
};
