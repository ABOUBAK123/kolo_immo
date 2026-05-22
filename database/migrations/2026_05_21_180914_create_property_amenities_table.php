<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_amenities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->enum('amenity', [
                'wifi', 'air_conditioning', 'equipped_kitchen', 'parking',
                'generator', 'pool', 'gym', 'security', 'tv', 'washer',
                'dryer', 'iron', 'workspace', 'balcony', 'garden',
                'elevator', 'hot_water', 'solar_power'
            ]);
            $table->timestamps();

            $table->unique(['property_id', 'amenity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_amenities');
    }
};
