<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'booking_id'     => Booking::factory(),
            'user_id'        => User::factory(),
            'transaction_id' => 'TXN-' . strtoupper(fake()->bothify('??########')),
            'amount'         => fake()->randomElement([15000, 25000, 35000, 50000, 75000, 100000]),
            'currency'       => 'XOF',
            'method'         => fake()->randomElement(['orange_money', 'wave', 'mtn_momo', 'moov_money']),
            'status'         => 'success',
            'type'           => 'payment',
            'paid_at'        => fake()->dateTimeBetween('-3 months', 'now'),
        ];
    }

    public function success(): static
    {
        return $this->state(['status' => 'success', 'paid_at' => now()]);
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending', 'paid_at' => null]);
    }

    public function failed(): static
    {
        return $this->state(['status' => 'failed', 'paid_at' => null]);
    }

    public function refund(): static
    {
        return $this->state(['type' => 'refund', 'status' => 'success']);
    }
}
