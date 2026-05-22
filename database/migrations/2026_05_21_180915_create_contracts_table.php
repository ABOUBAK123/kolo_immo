<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('pdf_path')->nullable();
            $table->enum('status', ['draft', 'pending_signature', 'signed', 'expired'])->default('draft');
            $table->timestamp('tenant_signed_at')->nullable();
            $table->string('tenant_signature_otp')->nullable();
            $table->timestamp('owner_signed_at')->nullable();
            $table->string('owner_signature_otp')->nullable();
            $table->string('entry_inspection_path')->nullable();
            $table->string('exit_inspection_path')->nullable();
            $table->timestamp('entry_inspection_signed_at')->nullable();
            $table->timestamp('exit_inspection_signed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
