<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 5)->default('XOF');
            $table->enum('method', ['orange_money', 'wave', 'mtn_money', 'moov_money', 'card', 'bank_transfer']);
            $table->string('phone_number', 20)->nullable();
            $table->enum('status', ['pending', 'processing', 'success', 'failed', 'refunded'])->default('pending');
            $table->enum('type', ['payment', 'refund', 'release'])->default('payment');
            $table->string('cinetpay_transaction_id')->nullable();
            $table->json('gateway_response')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'status']);
            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
