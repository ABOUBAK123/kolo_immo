<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('vat_percent', 5, 2)->default(0)->after('platform_commission');
            $table->decimal('vat_amount',  12, 2)->default(0)->after('vat_percent');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['vat_percent', 'vat_amount']);
        });
    }
};
