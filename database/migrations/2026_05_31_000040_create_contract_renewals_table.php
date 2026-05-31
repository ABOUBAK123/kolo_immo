<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_renewals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('initiated_by')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'expired'])->default('pending');
            $table->decimal('current_amount', 10, 2);     // loyer actuel
            $table->decimal('proposed_amount', 10, 2);    // nouveau loyer proposé
            $table->date('current_end_date');
            $table->date('proposed_end_date');
            $table->integer('additional_nights');
            $table->text('note')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->foreignId('responded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('termination_notice_at')->nullable()->after('cancelled_at');
            $table->date('termination_date')->nullable()->after('termination_notice_at');
            $table->string('termination_reason')->nullable()->after('termination_date');
            $table->foreignId('termination_by')->nullable()->after('termination_reason')
                  ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['termination_by']);
            $table->dropColumn(['termination_notice_at', 'termination_date', 'termination_reason', 'termination_by']);
        });
        Schema::dropIfExists('contract_renewals');
    }
};
