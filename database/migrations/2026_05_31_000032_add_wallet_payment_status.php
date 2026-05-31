<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'wallet' to payment_status enum on bookings
        DB::statement("ALTER TABLE bookings MODIFY COLUMN payment_status ENUM('pending','paid','escrowed','released','refunded','wallet','refund_pending') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("UPDATE bookings SET payment_status = 'pending' WHERE payment_status = 'wallet'");
        DB::statement("ALTER TABLE bookings MODIFY COLUMN payment_status ENUM('pending','paid','escrowed','released','refunded','refund_pending') NOT NULL DEFAULT 'pending'");
    }
};
