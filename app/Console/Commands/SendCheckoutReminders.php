<?php

namespace App\Console\Commands;

use App\Mail\CheckoutReminderMail;
use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendCheckoutReminders extends Command
{
    protected $signature   = 'checkout:remind';
    protected $description = 'Send checkout reminder emails 7 days and 1 day before check-out';

    public function handle(): int
    {
        $targets = [7, 1]; // days before check_out

        foreach ($targets as $days) {
            $targetDate = now()->addDays($days)->toDateString();

            $bookings = Booking::where('status', 'confirmed')
                ->whereDate('check_out', $targetDate)
                ->with(['property', 'tenant', 'owner'])
                ->get();

            $this->info("Bookings checking out in {$days} day(s): {$bookings->count()}");

            foreach ($bookings as $booking) {
                try {
                    // Notify tenant
                    if ($booking->tenant?->email) {
                        Mail::to($booking->tenant->email)->queue(
                            new CheckoutReminderMail($booking, $booking->tenant, $days, 'tenant')
                        );
                    }

                    // Notify owner
                    if ($booking->owner?->email) {
                        Mail::to($booking->owner->email)->queue(
                            new CheckoutReminderMail($booking, $booking->owner, $days, 'owner')
                        );
                    }

                    $this->info("  ✓ Reminded booking #{$booking->reference} ({$days}d)");
                } catch (\Throwable $e) {
                    Log::error("[CheckoutReminders] Booking #{$booking->id}: " . $e->getMessage());
                    $this->error("  ✗ Booking #{$booking->id} failed: " . $e->getMessage());
                }
            }
        }

        $this->info('Done.');
        return Command::SUCCESS;
    }
}
