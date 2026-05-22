<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('code', 6);
            $table->enum('purpose', ['phone_verify', 'login', 'contract_sign', 'password_reset']);
            $table->morphs('otpable');
            $table->boolean('used')->default(false);
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['phone', 'purpose', 'used']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
