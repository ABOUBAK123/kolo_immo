<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Dispute;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DisputeFactory extends Factory
{
    protected $model = Dispute::class;

    public function definition(): array
    {
        return [
            'booking_id'      => Booking::factory()->confirmed(),
            'opened_by'       => User::factory(),
            'reason'          => fake()->randomElement(['non_payment', 'damage', 'fraud', 'misrepresentation', 'refund_refused', 'other']),
            'description'     => fake()->paragraphs(2, true),
            'status'          => fake()->randomElement(['open', 'in_progress', 'resolved', 'closed']),
            'resolution'      => null,
            'admin_notes'     => null,
            'resolved_by'     => null,
            'resolved_at'     => null,
        ];
    }

    public function open(): static
    {
        return $this->state(['status' => 'open', 'resolved_at' => null]);
    }

    public function resolved(): static
    {
        return $this->state([
            'status'      => 'resolved',
            'resolution'  => fake()->randomElement(['refund_full', 'refund_partial', 'no_action']),
            'admin_notes' => fake()->sentence(),
            'resolved_at' => now(),
            'resolved_by' => User::factory()->state(['role' => 'admin']),
        ]);
    }
}
