<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('balance', 12, 2)->default(0);
            $table->string('currency', 5)->default('XOF');
            $table->timestamps();
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['credit', 'debit', 'refund', 'topup']);
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->string('description');
            $table->string('reference')->nullable()->unique();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('completed');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('wallets');
    }
};
