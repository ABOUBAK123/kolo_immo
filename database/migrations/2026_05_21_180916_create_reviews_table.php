<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['tenant_to_property', 'owner_to_tenant'])->default('tenant_to_property');
            $table->tinyInteger('rating_overall');
            $table->tinyInteger('rating_cleanliness')->nullable();
            $table->tinyInteger('rating_communication')->nullable();
            $table->tinyInteger('rating_accuracy')->nullable();
            $table->tinyInteger('rating_location')->nullable();
            $table->tinyInteger('rating_value')->nullable();
            $table->text('comment')->nullable();
            $table->text('owner_reply')->nullable();
            $table->timestamp('owner_replied_at')->nullable();
            $table->boolean('is_flagged')->default(false);
            $table->string('flag_reason')->nullable();
            $table->timestamps();

            $table->unique(['booking_id', 'reviewer_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
