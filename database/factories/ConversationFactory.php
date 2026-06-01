<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Conversation;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    public function definition(): array
    {
        $owner  = User::factory()->state(['role' => 'owner'])->create();
        $tenant = User::factory()->state(['role' => 'tenant'])->create();

        return [
            'property_id'     => Property::factory()->withOwner($owner),
            'tenant_id'       => $tenant->id,
            'owner_id'        => $owner->id,
            'booking_id'      => null,
            'last_message_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    public function withBooking(): static
    {
        return $this->afterCreating(function (Conversation $conv) {
            $booking = Booking::factory()->forProperty($conv->property)->create([
                'tenant_id' => $conv->tenant_id,
                'owner_id'  => $conv->owner_id,
            ]);
            $conv->update(['booking_id' => $booking->id]);
        });
    }
}
