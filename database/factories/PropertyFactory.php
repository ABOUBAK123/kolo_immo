<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PropertyFactory extends Factory
{
    protected $model = Property::class;

    public function definition(): array
    {
        $cities = ['Abidjan', 'Dakar', 'Bamako', 'Ouagadougou', 'Lomé', 'Cotonou', 'Conakry'];
        $types  = ['studio', 'appartement', 'villa', 'chambre', 'duplex', 'maison'];

        return [
            'owner_id'            => User::factory()->state(['role' => 'owner']),
            'title'               => fake()->sentence(5),
            'description'         => fake()->paragraphs(2, true),
            'type'                => fake()->randomElement($types),
            'country'             => fake()->randomElement(["Côte d'Ivoire", 'Sénégal', 'Mali', 'Togo']),
            'city'                => fake()->randomElement($cities),
            'district'            => fake()->word(),
            'address'             => fake()->streetAddress(),
            'latitude'            => fake()->latitude(2, 15),
            'longitude'           => fake()->longitude(-18, 5),
            'capacity'            => fake()->numberBetween(1, 8),
            'bedrooms'            => fake()->numberBetween(1, 5),
            'bathrooms'           => fake()->numberBetween(1, 3),
            'area_sqm'            => fake()->numberBetween(25, 200),
            'price_per_night'     => fake()->randomElement([10000, 15000, 20000, 25000, 35000, 50000, 75000]),
            'price_per_week'      => null,
            'price_per_month'     => null,
            'deposit_amount'      => fake()->randomElement([0, 5000, 10000, 20000]),
            'booking_type'        => fake()->randomElement(['request', 'instant']),
            'cancellation_policy' => fake()->randomElement(['flexible', 'moderate', 'strict']),
            'allow_pets'          => fake()->boolean(20),
            'allow_smoking'       => fake()->boolean(15),
            'allow_parties'       => fake()->boolean(10),
            'check_in_time'       => fake()->randomElement(['12:00', '14:00', '15:00', '16:00']),
            'check_out_time'      => fake()->randomElement(['10:00', '11:00', '12:00']),
            'status'              => 'active',
            'verification_status' => 'pending',
            'rating_avg'          => fake()->randomFloat(1, 3, 5),
            'rating_count'        => fake()->numberBetween(0, 50),
            'views_count'         => fake()->numberBetween(0, 500),
            'is_featured'         => fake()->boolean(15),
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => 'active', 'verification_status' => 'verified']);
    }

    public function pending(): static
    {
        return $this->state(['status' => 'inactive', 'verification_status' => 'pending']);
    }

    public function featured(): static
    {
        return $this->state(['is_featured' => true, 'status' => 'active', 'verification_status' => 'verified']);
    }

    public function withOwner(User $owner): static
    {
        return $this->state(['owner_id' => $owner->id]);
    }
}
