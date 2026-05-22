<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->date('check_in');
            $table->date('check_out');
            $table->integer('nights');
            $table->integer('guests')->default(1);
            $table->decimal('price_per_night', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('service_fee', 12, 2)->default(0);
            $table->decimal('platform_commission', 12, 2)->default(0);
            $table->decimal('deposit_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', [
                'pending', 'confirmed', 'cancelled', 'completed',
                'refund_pending', 'refunded', 'disputed'
            ])->default('pending');
            $table->enum('payment_status', ['pending', 'paid', 'escrowed', 'released', 'refunded'])->default('pending');
            $table->text('special_requests')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->enum('cancelled_by', ['tenant', 'owner', 'admin'])->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->timestamp('funds_released_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'payment_status']);
            $table->index(['tenant_id', 'status']);
            $table->index(['owner_id', 'status']);
            $table->index(['property_id', 'check_in', 'check_out']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
