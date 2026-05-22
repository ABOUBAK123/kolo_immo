<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Property;
use App\Models\PropertyBlockedDate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingService
{
    /**
     * Calculate the price breakdown for a booking.
     *
     * - subtotal     = nights × price_per_night
     * - service_fee  = subtotal × 3%   (platform service fee charged to tenant)
     * - commission   = subtotal × 8%   (platform commission charged to owner at payout)
     * - total        = subtotal + service_fee + deposit
     */
    public function calculatePrice(
        Property $property,
        string $checkIn,
        string $checkOut,
        int $guests
    ): array {
        $start  = Carbon::parse($checkIn);
        $end    = Carbon::parse($checkOut);
        $nights = $start->diffInDays($end);

        if ($nights < 1) {
            $nights = 1;
        }

        $subtotal   = $nights * $property->price_per_night;
        $serviceFee = round($subtotal * 0.03, 0);
        $commission = round($subtotal * 0.08, 0);
        $deposit    = $property->deposit_amount ?? 0;
        $total      = $subtotal + $serviceFee + $deposit;

        return [
            'nights'               => $nights,
            'price_per_night'      => $property->price_per_night,
            'subtotal'             => $subtotal,
            'service_fee'          => $serviceFee,
            'platform_commission'  => $commission,
            'deposit_amount'       => $deposit,
            'total_amount'         => $total,
        ];
    }

    /**
     * Create a new booking.
     */
    public function createBooking(
        Property $property,
        User $tenant,
        array $data
    ): Booking {
        $checkIn  = $data['check_in'];
        $checkOut = $data['check_out'];
        $guests   = $data['guests'] ?? 1;

        // Verify availability
        if (!$property->isAvailable($checkIn, $checkOut)) {
            throw new \RuntimeException('Ce logement n\'est pas disponible pour les dates sélectionnées.');
        }

        $pricing = $this->calculatePrice($property, $checkIn, $checkOut, $guests);

        return DB::transaction(function () use ($property, $tenant, $data, $pricing, $checkIn, $checkOut, $guests) {
            $booking = Booking::create([
                'reference'           => Booking::generateReference(),
                'property_id'         => $property->id,
                'tenant_id'           => $tenant->id,
                'owner_id'            => $property->owner_id,
                'check_in'            => $checkIn,
                'check_out'           => $checkOut,
                'nights'              => $pricing['nights'],
                'guests'              => $guests,
                'price_per_night'     => $pricing['price_per_night'],
                'subtotal'            => $pricing['subtotal'],
                'service_fee'         => $pricing['service_fee'],
                'platform_commission' => $pricing['platform_commission'],
                'deposit_amount'      => $pricing['deposit_amount'],
                'total_amount'        => $pricing['total_amount'],
                'status'              => $property->booking_type === 'instant' ? 'confirmed' : 'pending',
                'payment_status'      => 'pending',
                'special_requests'    => $data['special_requests'] ?? null,
                'confirmed_at'        => $property->booking_type === 'instant' ? now() : null,
            ]);

            // Block dates if instant booking
            if ($property->booking_type === 'instant') {
                PropertyBlockedDate::create([
                    'property_id' => $property->id,
                    'start_date'  => $checkIn,
                    'end_date'    => $checkOut,
                    'reason'      => 'Réservation #' . $booking->reference,
                    'source'      => 'booking',
                ]);
            }

            // Create a conversation thread
            $property->bookings()->where('id', $booking->id)->first();

            \App\Models\Conversation::create([
                'booking_id'  => $booking->id,
                'property_id' => $property->id,
                'tenant_id'   => $tenant->id,
                'owner_id'    => $property->owner_id,
                'last_message_at' => now(),
            ]);

            Log::info('[KOLO IMMO] Nouvelle réservation créée', [
                'reference' => $booking->reference,
                'property'  => $property->title,
                'tenant'    => $tenant->name,
            ]);

            return $booking;
        });
    }

    /**
     * Confirm a pending booking (for request-type properties).
     */
    public function confirmBooking(Booking $booking): Booking
    {
        if (!$booking->isPending()) {
            throw new \RuntimeException('Cette réservation ne peut pas être confirmée.');
        }

        DB::transaction(function () use ($booking) {
            $booking->update([
                'status'       => 'confirmed',
                'confirmed_at' => now(),
            ]);

            // Block dates
            PropertyBlockedDate::create([
                'property_id' => $booking->property_id,
                'start_date'  => $booking->check_in->toDateString(),
                'end_date'    => $booking->check_out->toDateString(),
                'reason'      => 'Réservation #' . $booking->reference,
                'source'      => 'booking',
            ]);
        });

        return $booking->fresh();
    }

    /**
     * Cancel a booking.
     */
    public function cancelBooking(Booking $booking, string $cancelledBy, string $reason = ''): Booking
    {
        if (!$booking->isCancellable()) {
            throw new \RuntimeException('Cette réservation ne peut plus être annulée.');
        }

        DB::transaction(function () use ($booking, $cancelledBy, $reason) {
            $booking->update([
                'status'              => 'cancelled',
                'cancelled_at'        => now(),
                'cancelled_by'        => $cancelledBy,
                'cancellation_reason' => $reason,
                'payment_status'      => $booking->payment_status === 'escrowed' ? 'refunded' : $booking->payment_status,
            ]);

            // Remove blocked dates created by this booking
            PropertyBlockedDate::where('property_id', $booking->property_id)
                ->where('source', 'booking')
                ->where('reason', 'like', '%' . $booking->reference . '%')
                ->delete();
        });

        return $booking->fresh();
    }

    /**
     * Release funds to the owner after successful stay completion.
     */
    public function releaseFunds(Booking $booking): Booking
    {
        if ($booking->status !== 'completed') {
            throw new \RuntimeException('Les fonds ne peuvent être libérés que pour une réservation terminée.');
        }

        if ($booking->payment_status !== 'escrowed') {
            throw new \RuntimeException('Les fonds ne sont pas en séquestre pour cette réservation.');
        }

        $booking->update([
            'payment_status'    => 'released',
            'funds_released_at' => now(),
        ]);

        Log::info('[KOLO IMMO] Fonds libérés', [
            'booking'   => $booking->reference,
            'amount'    => $booking->subtotal - $booking->platform_commission,
        ]);

        return $booking->fresh();
    }
}
