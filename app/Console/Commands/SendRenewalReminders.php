<?php

namespace App\Console\Commands;

use App\Mail\RenewalReminderMail;
use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendRenewalReminders extends Command
{
    protected $signature   = 'renewals:remind';
    protected $description = 'Send renewal reminder emails 30 and 60 days before booking end';

    public function handle(): int
    {
        $targets = [60, 30]; // days before check_out

        foreach ($targets as $days) {
            $targetDate = now()->addDays($days)->toDateString();

            $bookings = Booking::where('status', 'confirmed')
                ->whereDate('check_out', $targetDate)
                ->whereNull('termination_notice_at')
                ->with(['property', 'tenant', 'owner'])
                ->get();

            $this->info("Bookings ending in {$days} days: {$bookings->count()}");

            foreach ($bookings as $booking) {
                try {
                    foreach ([$booking->tenant, $booking->owner] as $user) {
                        if ($user && $user->email) {
                            Mail::to($user->email)->queue(
                                new RenewalReminderMail($booking, $user, $days)
                            );
                        }
                    }
                    $this->info("  ✓ Reminded booking #{$booking->reference}");
                } catch (\Throwable $e) {
                    Log::error("[RenewalReminders] Booking #{$booking->id}: " . $e->getMessage());
                }
            }
        }

        $this->info('Done.');
        return Command::SUCCESS;
    }
}
