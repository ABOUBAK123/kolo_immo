<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        $overall = fake()->numberBetween(3, 5);

        return [
            'booking_id'           => Booking::factory()->completed(),
            'reviewer_id'          => User::factory(),
            'property_id'          => null,
            'reviewed_user_id'     => null,
            'type'                 => 'tenant_to_property',
            'rating_overall'       => $overall,
            'rating_cleanliness'   => fake()->numberBetween(1, 5),
            'rating_communication' => fake()->numberBetween(1, 5),
            'rating_accuracy'      => fake()->numberBetween(1, 5),
            'rating_location'      => fake()->numberBetween(1, 5),
            'rating_value'         => fake()->numberBetween(1, 5),
            'comment'              => fake()->paragraph(),
            'owner_reply'          => fake()->boolean(30) ? fake()->sentence() : null,
            'owner_replied_at'     => null,
            'is_flagged'           => false,
        ];
    }

    public function forProperty(int $propertyId, int $reviewerId): static
    {
        return $this->state([
            'property_id' => $propertyId,
            'reviewer_id' => $reviewerId,
            'type'        => 'tenant_to_property',
        ]);
    }

    public function positive(): static
    {
        return $this->state([
            'rating_overall'       => 5,
            'rating_cleanliness'   => 5,
            'rating_communication' => 5,
            'rating_accuracy'      => 5,
            'rating_location'      => 5,
            'rating_value'         => 5,
        ]);
    }
}
