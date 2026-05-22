<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['studio', 'apartment', 'villa', 'room', 'duplex'])->default('apartment');
            $table->string('country', 2)->default('CI');
            $table->string('city');
            $table->string('district')->nullable();
            $table->text('address');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('capacity')->default(1);
            $table->integer('bedrooms')->default(1);
            $table->integer('bathrooms')->default(1);
            $table->integer('area_sqm')->nullable();
            $table->decimal('price_per_night', 12, 2);
            $table->decimal('price_per_week', 12, 2)->nullable();
            $table->decimal('price_per_month', 12, 2)->nullable();
            $table->decimal('deposit_amount', 12, 2)->default(0);
            $table->enum('booking_type', ['instant', 'request'])->default('request');
            $table->enum('cancellation_policy', ['flexible', 'moderate', 'strict'])->default('moderate');
            $table->boolean('allow_pets')->default(false);
            $table->boolean('allow_smoking')->default(false);
            $table->boolean('allow_parties')->default(false);
            $table->time('check_in_time')->default('14:00:00');
            $table->time('check_out_time')->default('11:00:00');
            $table->string('cover_photo')->nullable();
            $table->enum('status', ['draft', 'active', 'inactive', 'suspended'])->default('draft');
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->integer('rating_count')->default(0);
            $table->integer('views_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'country', 'city']);
            $table->index(['owner_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
