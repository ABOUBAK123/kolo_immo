<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $checkIn  = Carbon::instance(fake()->dateTimeBetween('-3 months', '+3 months'));
        $nights   = fake()->numberBetween(1, 30);
        $checkOut = $checkIn->copy()->addDays($nights);
        $pricePerNight = fake()->randomElement([10000, 15000, 20000, 25000, 35000, 50000]);
        $subtotal   = $nights * $pricePerNight;
        $serviceFee = round($subtotal * config('kolo.service_fee_percent', 3) / 100);
        $commission = round($subtotal * config('kolo.platform_commission_percent', 8) / 100);
        $deposit    = fake()->randomElement([0, 5000, 10000]);
        $vat        = 0;
        $total      = $subtotal + $serviceFee + $vat + $deposit;

        return [
            'reference'          => 'KI-' . strtoupper(fake()->bothify('????????')),
            'property_id'        => Property::factory(),
            'tenant_id'          => User::factory()->state(['role' => 'tenant']),
            'owner_id'           => User::factory()->state(['role' => 'owner']),
            'check_in'           => $checkIn->toDateString(),
            'check_out'          => $checkOut->toDateString(),
            'nights'             => $nights,
            'guests'             => fake()->numberBetween(1, 4),
            'price_per_night'    => $pricePerNight,
            'subtotal'           => $subtotal,
            'service_fee'        => $serviceFee,
            'platform_commission'=> $commission,
            'vat_percent'        => 0,
            'vat_amount'         => $vat,
            'deposit_amount'     => $deposit,
            'total_amount'       => $total,
            'status'             => fake()->randomElement(['pending', 'confirmed', 'completed', 'cancelled']),
            'payment_status'     => fake()->randomElement(['pending', 'paid', 'escrowed']),
            'special_requests'   => fake()->boolean(20) ? fake()->sentence() : null,
            'confirmed_at'       => null,
        ];
    }

    public function confirmed(): static
    {
        return $this->state([
            'status'         => 'confirmed',
            'payment_status' => 'escrowed',
            'confirmed_at'   => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state([
            'status'             => 'completed',
            'payment_status'     => 'released',
            'confirmed_at'       => now()->subDays(10),
            'funds_released_at'  => now(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending', 'payment_status' => 'pending']);
    }

    public function cancelled(): static
    {
        return $this->state([
            'status'              => 'cancelled',
            'cancelled_at'        => now(),
            'cancelled_by'        => 'tenant',
            'cancellation_reason' => fake()->sentence(),
        ]);
    }

    public function forProperty(Property $property): static
    {
        return $this->state([
            'property_id' => $property->id,
            'owner_id'    => $property->owner_id,
        ]);
    }
}
